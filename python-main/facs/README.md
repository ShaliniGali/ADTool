# Role Service

Service designed to interact with all Rhombus Power applications. This service allows/disallows application feature access based based off certain user roles associated with each users Keycloak token(profile). 

# Notes for Endpoints (Will be refined later)

# Ingest (POST) - returns 201 ok if db insertion is successful
json body

{
    "app": "appname"
    "feature": "feature-name"
    "roles": [list-of-roles]
}

----------------------

# hasAccess(GET) - returns a bool yes or no
json body
{
    "app": "appname"
    "feature": "feature-name"
    "persons-roles": [list-of-roles]
}

-----------------------

# deleteFeature(DELETE)
json body
{
    "app": "appname"
    "feature": "feature-name"
}


----------------------
# Info

Levels: RootAdmin, Admin, Dev, Testing.... list can be appended in the program

There can only be one [RootAdmin].
Only [RootAdmin] can create api keys for rest of the levels.
[Admin, Dev] roles can create Role/App/SubApp/Features
[Admin] can perform delete operations on Role/App/SubApp/Features
[Admin] can create role_map
[Anyone] can access hasaccess

All above mentioned permissions are flexible and can be altered at one file. 
