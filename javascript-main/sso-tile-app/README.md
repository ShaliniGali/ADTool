# FILE OPERATIONS MICROSERVICE


This API allows for various file operations, mainly involving interating with AWS S3


## /static
This endpoint shows the static tiles pages

## /s3/get

### GET
Fetches a file from AWS S3

Supported attributes:

| Attribute                | Type        | Required | Description           |
|:-------------------------|:---------   |:---------|:----------------------|
| `path`                   | Query Param | Yes      | The full path to the requested file 

If successful, returns the file with a status code of 200

## /s3/list

### GET
Lists all files in the S3 Bucket

Supported attributes:

| Attribute                | Type        | Required | Description           |
|:-------------------------|:---------   |:---------|:----------------------|
| `prefix`                 | Query Param | No       | An optional prefix to narrow search results

If successful, returns 200 and the following
response attributes:

| Attribute                | Type     | Description           |
|:-------------------------|:---------|:----------------------|
| `count`              | Integer | Total amount of files listed |
| `data`  | Array | An array of objects containing the Metadata for each file |

Here is an example of the file Metadata

```json:table
{
	"Key": "test-aws/randfile",
	"LastModified": "2022-12-14T23:39:00.000Z",
	"ETag": "\"8c8d845234561c5d3729acv35b9cc111\"",
	"ChecksumAlgorithm": [],
	"Size": 1024,
	"StorageClass": "STANDARD"
}
```
## /s3/verify
### GET

Checks if a file exists in the S3 bucket
| Attribute                | Type        | Required | Description           |
|:-------------------------|:---------   |:---------|:----------------------|
| `path`                   | Query Param | Yes      | The full path to the requested file 

If successful, returns 200 and the following response attributes:

| Attribute                | Type     | Description           |
|:-------------------------|:---------|:----------------------|
| `fileExists`              | Boolean | If the requested file exists in the S3 bucket |
| `metadata`  | Object / Null |  If the file exists, its metadata is returned in the response|

## /s3/delete
### DELETE

Deletes a file from the S3 bucket
| Attribute                | Type        | Required | Description           |
|:-------------------------|:---------   |:---------|:----------------------|
| `path`                   | Query Param | Yes      | The full path to the file to delete

If successful, returns 202 and the following response attributes:

| Attribute                | Type     | Description           |
|:-------------------------|:---------|:----------------------|
| `DeleteMarker`              | Boolean | If the file was marked as deleted. Note that S3 does not actually delete the file|
| `VersionId`  | String |  The version ID of the DeleteMarker, deleting this DeleteMarker will recover the original file|

## /s3/move
### PUT

Renames a file within the S3 bucket

| Attribute                | Type        | Required | Description           |
|:-------------------------|:---------   |:---------|:----------------------|
| `path`                   | Query Param | Yes      | The path to the source file
| `newPath`                   | Query Param | Yes      | The new location for the file

Note that this functions by copying the object to a new location, then deleting the original.
If successful, returns 204 and the following response attributes:

| Attribute                | Type     | Description           |
|:-------------------------|:---------|:----------------------|
| `deleteData`              | Object | The response data for the deletion of the original file|
| `copyData`  | Object |  The information about the new copied object|
## /s3/upload
### POST

Uploads a file into AWS S3
Requires your DNS name to be whitelisted in the .ENV

| Attribute                | Type        | Required | Description           |
|:-------------------------|:---------   |:---------|:----------------------|
| `path`                   | Query Param | No      | optional prefix for the uploaded file


If successful, returns 201 and the following response attributes:

| Attribute                | Type     | Description           |
|:-------------------------|:---------|:----------------------|
| `location`              | String | The path of the uploaded file in AWS S3|

Example request
```
curl --request POST \
  --url 'http://localhost:3000/s3/upload?path=test-aws' \
  --header 'Content-Type: multipart/form-data' \
  --form =@/Path/to/file.txt
  ```
