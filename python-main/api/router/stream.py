from typing import Dict, List
from api.internal.redis_cache import (
    get_redis_decoded,
)

from typing import Dict, Any
from pydantic import BaseModel

from fastapi import (
    APIRouter,
    HTTPException, 
    Depends, 
    Body
)

from authentication.auth import (
    # get_current_user,
    UserRole,
    require_roles,
)


router = APIRouter(
    prefix="/stream",
    tags=["SSE/Stream SOCOM Endpoints"],
    responses={404:{"description":"Endpoint Not Found"}}
)


###PYDANTIC MODELS
class AckMessage(BaseModel):
    user_id: str
    message_ids: List[str]


##################
# Create the stream and consumer group if not already created
async def create_notif_stream_consumer(user_id):
    redis_client = get_redis_decoded()

    stream = "SOCOM::DT_UPLOADS::NOTIF" #RedisStreamBase("SOCOM::DT_UPLOADS::NOTIF")
    consumer_group = f"SOCOM::DT_NOTIF::{user_id}"

    try:
        #auto create stream if doesnt exist
        redis_client.xgroup_create(stream, consumer_group, id="$", mkstream=True)
    except Exception as e: #already created consumer group
        print(e)
        pass
    return consumer_group


@router.post("/dt/notif")
async def dt_notif_publish_message(message: Dict[str,Any],redis = Depends(get_redis_decoded),user=Depends(require_roles(UserRole.ADMIN))):
    #accept any form of json message input
    # breakpoint()
    """Publish a message to the shared DT topic stream."""    
    stream = "SOCOM::DT_UPLOADS::NOTIF"
    #auto creating stream if not exist
    msg_id = redis.xadd(stream,message,maxlen=100,) #current timestamp, soft trim
    return {"status": "Message Published", "id": msg_id}


@router.get("/dt/notif/messages/{limit}")
async def consume_last_n_messages(user_id:int, limit:int,redis=Depends(get_redis_decoded),user=Depends(require_roles(UserRole.ADMIN))): #for now, use int
    """Consume last 2 messages from Redis stream based on consumer group and consumer id"""
    consumer_group = await create_notif_stream_consumer(user_id)
    stream = "SOCOM::DT_UPLOADS::NOTIF"
   
    # Check pending messages
    pending_info = redis.xpending_range(stream,consumer_group, "-", "+",count=5)
    pending_ids = [msg["message_id"] for msg in pending_info]

    pending_msg = []    
    if pending_ids and isinstance(pending_ids, list):
        #min idle time of message in pending before being reserved
        pending_msg = redis.xclaim(stream, groupname=consumer_group, consumername="consumer_pending", min_idle_time=0, message_ids=pending_ids,retrycount=3)

    # breakpoint()
    # Read messages from the stream for the given consumer group
    # xreadgroup will get the last 2 messages that haven't been acknowledged yet
    # `count=2` to get the last 2 unread messages
    stream_messages = redis.xreadgroup(
        groupname=consumer_group,
        consumername="consumer_read",
        streams={stream: ">"},  # '>' means the unread messages
        count=limit,
        block=300  # Blocking with a timeout of 0 (do not block indefinitely)
    )

    if stream_messages:
        stream_messages = stream_messages[0][1]

    
    return {"stream_messages": stream_messages,
            "pending_messages":pending_msg,
            "stream_name":stream,
            "consumer_group":consumer_group}

@router.post("/dt/notif/acknowledge")
async def acknowledge_message(ack_msg: AckMessage,redis=Depends(get_redis_decoded),user=Depends(require_roles(UserRole.ADMIN))):
    user_id = ack_msg.user_id
    message_ids = ack_msg.message_ids
    stream = "SOCOM::DT_UPLOADS::NOTIF"
    consumer_group = await create_notif_stream_consumer(user_id)
    try:
        pending_info = redis.xpending_range(stream, consumer_group, "-", "+",count=1000) #max pending size
        
        pending_ids = {msg["message_id"] for msg in pending_info} 
        ack_ids = [msg_id for msg_id in message_ids if msg_id in pending_ids]
        if not ack_ids:
            raise HTTPException(404,"message ids or consumer groups are not valid")
        
        redis.xack(stream, consumer_group, *ack_ids)

        #prune pending queue down to max size of 20
        if len(pending_info) > 20:
            # Automatically acknowledge old messages to limit pending size to 100
            # This will acknowledge messages exceeding the limit
            pending_ids_to_ack = [msg["message_id"] for msg in pending_info[:len(pending_info)-20]]
            redis.xack(stream, consumer_group, *pending_ids_to_ack)

    except Exception as e:
        print(e)
        raise HTTPException(404,"Please double check the stream, consumer group or the message ids")
    return {"message": f"Message {','.join(message_ids)} acknowledged for user {user_id}"}