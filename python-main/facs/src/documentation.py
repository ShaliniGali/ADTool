api_description = """
FACS API Service designed to interact with all Rhombus Power applications. 
This service allows/disallows application feature access based based off 
certain user roles associated with each users Keycloak token(profile). 🚀

## User Types

Valid user_types for FACS are **admin,dev, app**

## API

Admins and NPEs accessing this service will
be able to:

* **Perform data ingest to add/update features with roles**.
* **Ask for access control decisions using the has_access endpoint**.
* **Admins have access to all endpoints for full CRUD operations**.

"""
