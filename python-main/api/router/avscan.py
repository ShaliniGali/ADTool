from fastapi import APIRouter, HTTPException, File, UploadFile
import os
import clamd

router = APIRouter(
    prefix="",
    tags=["AVSCAN"],
    responses={404:{"description":"Endpoint Not Found"}}
)


@router.post("/avscan_uploadfile")
async def create_upload_file(file: UploadFile=File(...)):
    """ <h3> Virus scan of uploaded file </h3>

    Input parameter
    - **file** (Required): Uplaoding file. (multipart/form-data)

    Output JSON
    - **status** (str): Status of virus scan, 'OK', 'FOUND', 'ERROR'
    - **message** (str): Result message of virus scan.
    - **filename** (str): File name.


    """
    response = {}
    try:
        cd = clamd.ClamdNetworkSocket()
        cd.__init__(host=os.environ.get('SOCOM_CLAMD_NETWORK_ALIAS'), port=int(os.environ.get('SOCOM_CLAMD_PORT')), timeout=None)
        scan_result = cd.instream(file.file) # async file chunk

        if (scan_result['stream'][0] == 'OK'):
            status = 'OK'
            message = 'File has no virus'
            print(scan_result['stream'])
        elif (scan_result['stream'][0] == 'FOUND'):
            status = 'FOUND'
            message = f'File has virus ({scan_result["stream"][1]})'
            print(scan_result['stream'])
        else:
            status = 'ERROR'
            message = 'Error occured while virus scanning'
        response['status'] = status
        response['message'] = message
        response['filename'] = file.filename
    except clamd.ConnectionError as e:
        raise HTTPException(
            status_code=500,
            detail='Connection refused to Clamd: ' + str(e)
    )
    return response