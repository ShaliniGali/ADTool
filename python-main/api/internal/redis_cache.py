import redis
import os
import json
import sys
from pathlib import Path

current_path = Path(os.path.abspath(__file__))
sys.path.append(str(current_path.parent.parent.parent))

from api.internal import conn

from typing import Dict, List
from fastapi import HTTPException

import pandas as pd
import pickle
import io

################
SOCOM_REDIS_HOST = os.environ.get("SOCOM_REDIS_HOST", 'localhost')
SOCOM_REDIS_PORT = os.environ.get("SOCOM_REDIS_PORT", '6379')
SOCOM_REDIS_DB = os.environ.get("SOCOM_REDIS_DB",0)

def create_redis(decode_responses):
  return redis.ConnectionPool(
    host=SOCOM_REDIS_HOST, 
    port=SOCOM_REDIS_PORT, 
    db=SOCOM_REDIS_DB, 
    decode_responses=decode_responses #default False
  )

def _get_redis(redis_pool):
    return redis.Redis(connection_pool=redis_pool)

def get_redis_nondecoded():
    return _get_redis(redis_pool_nondecoded)
    
def get_redis_decoded():
    return _get_redis(redis_pool_decoded)


class RedisController:
    @staticmethod
    def save_pickle_redis(data,key:str,redis_cache,expires_in:int=None):
        """
        Pickle data and save it into redis_cache under key:obj
        Note: decode responses must be false for connection
        Args:
            data: data to be pickled
            key (str): cache key to be put into the cache. If exists, overwrite with new data
            redis_cache: cache
        """
        try:
            obj = pickle.dumps(data)
            status = redis_cache.set(key, obj,ex=expires_in) if expires_in else redis_cache.set(key, obj)
            print(f'Pickled file successfully saved to Redis as: {key}!')
            print(status)
        except Exception as e:
            raise HTTPException(status_code=404, detail=e)
    
    @staticmethod
    def get_pickle_redis(key:str,redis_cache):
        """
        Obtain unpicked data from the redis cache with given key. Return None if key is not found.
        Note: decode_responses must be false for connection
        Args:
            key (str): key from the cache
            redis_cache: cache
        Return:
            unpacked_obj: unpickled object from the redis cache. If not exist, return None
        """        
        # print(key)
        try:
            obj = redis_cache.get(key)
            # print(obj)
            if obj:
                unpacked_obj = pickle.loads(obj)
                print(f"Data retrieved successfully unpickled from Redis: {key}!")
            else:
                unpacked_obj = None
        except Exception as e:
            raise HTTPException(status_code=404, detail=e)
        # print(unpacked_obj)
        return unpacked_obj

    @staticmethod
    def save_parquet_redis(data,key:str,redis_cache,expires_in=None):
        """
        Save a parquet object into redis
        Note: decode_responses must be false for connection
        Args:
            data (parquet): data to save
            key (str): key from the cache
            redis_cache: cache
            expires_in (int): the number of seconds for key to expire
        Return:
            None
        """
        try:
            
            status = redis_cache.set(key,data,ex=expires_in) if expires_in else redis_cache.set(key, data)
            print(f'Parquet file successfully saved to Redis as: {key}!')
            print(status)
        except Exception as e:
            raise HTTPException(status_code=404, detail=e)
    
    @staticmethod
    def get_parquet_redis(key:str,redis_cache):
        """
        Obtain parquet data from the redis cache with given key. Return None if key is not found.
        Note: decode_responses must be false for connection
        Args:
            key (str): key from the cache
            redis_cache: cache
        Return:
            obj: unpacked object ready for pd.read_parquet()
        """        
        # print(key)
        try:
            obj = redis_cache.get(key)
            if obj:
                obj = io.BytesIO(obj)
                print(f"Data retrieved successfully from Redis: {key}!")
            else:
                obj = None
        except Exception as e:
            raise HTTPException(status_code=404, detail=e)
        # print(obj)
        return obj
    

    @staticmethod
    def write_json_to_redis(key,val,redis,expires_in=None):
        """
        Write a json dumps into redis with (key,val), replace existing values if exists
        Args:
            key(str): key of the cache
            val (json dumps): value of the json dump
            redis: redis cache
            expires_in(str): expires time after putting in the cache
        return:
            None
        """
        try:
            redis.set(key,val,ex=expires_in) if expires_in else redis.set(key,val)
            print(f'JSON file successfully saved to Redis as: {key}!')
        except Exception as e:
            raise HTTPException(status_code=404, detail=e)
        

    @staticmethod
    def get_json_from_redis(key,redis):
        """
        Retrieve JSON from Redis as a dictionary output
        Args:
            key (str): cache key
            redis: cache
        Return:
            data (dict): dictionary of the json output, None if key not found
        """
        data = None
        try:
            data = redis.get(key)
            # print(data)
            data = json.loads(data)
            print(f'JSON file successfully retrieved from Redis as: {key}!')
            return data
        except Exception as e:
            raise HTTPException(status_code=404, detail=e)        
        return None
    
    @staticmethod
    def update_dict_to_json_redis(key:str,value:Dict,redis):
        """
        Update previous JSON dictionary on Redis. 
        New keys will be added, old keys will be updated in values
        Only applicable to dict {} json storage, not List
        Args:
            key (str): cache key
            value (dict): value to append to the current value field
            redis: cache
        Return:
            None
        """        
        data = redis.get(key)
        if not data:
            redis.set(key,json.dumps(value))
            print("Key doesn't exist/evicted, JSON successfully inserted")
            return
        
        data = json.loads(data)
        data.update(value)
        redis.set(key,json.dumps(data))
        print("JSON successfully updated")



redis_pool_nondecoded = create_redis(decode_responses=False)
redis_pool_decoded = create_redis(decode_responses=True)