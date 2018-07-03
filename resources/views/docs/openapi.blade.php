openapi: "3.0.1"

info:
  title: "HIV API Specification"
  description: "For using the HIV API"
  version: "v1"
  contact:
    name: "Ayup Digital"
    url: "https://ayup.agency"

servers:
- url: "https://api.hiv.test/v1/"
  description: "Dev Server"

paths:
  /users:
    post:
      tags:
        - "Users"
      summary: "Create a new user"
      operationId: "users.store"
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: "#/components/schemas/ModifyUser"
      responses:
        200:
          description: "Successful response"
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/User"
    get:
      tags:
        - "Users"
      summary: "List all users"
      operationId: "users.list"
      responses:
        200:
          description: "Successful response"
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/UserCollection"
  /users/{id}:
    get:
      tags:
        - "Users"
      summary: "Get a specific user"
      operationId: "users.show"
      parameters:
        - $ref: "#/components/parameters/id"
      responses:
        200:
          description: "Successful response"
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/User"
    put:
      tags:
        - "Users"
      summary: "Update a specific user"
      operationId: "users.update"
      parameters:
        - $ref: "#/components/parameters/id"
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: "#/components/schemas/ModifyUser"
      responses:
        200:
          description: "Successful response"
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/User"
    delete:
      tags:
        - "Users"
      summart: "Delete a specific user"
      operationId: "users.destroy"
      parameters:
        - $ref: "#/components/parameters/id"
      responses:
        200:
          description: "Successful response"
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/DeletedResponse"

components:
  securitySchemes:
    app_id:
      type: "apiKey"
      description: "API key to authorize requests"
      name: "appid"
      in: "query"
  parameters:
    id:
      name: "id"
      in: "path"
      description: "The ID of the model"
      schema:
        type: "integer"
  schemas:
    DeletedResponse:
      type: "object"
      properties:
        message:
          type: "string"
          example: "Model successfully deleted"
    ApiResourceMeta:
      type: "object"
      properties:
        current_page:
          type: "integer"
        from:
          type: "integer"
        last_page:
          type: "integer"
        path:
          type: "string"
          example: "http://example.com/pagination"
        per_page:
          type: "integer"
        to:
          type: "integer"
        total:
          type: "integer"
    ApiResourceLinks:
      type: "object"
      properties:
        first:
          type: "string"
          example: "http://example.com/pagination?page=1"
        last:
          type: "string"
          example: "http://example.com/pagination?page=1"
        prev:
          type: "string"
          nullable: true
        next:
          type: "string"
          nullable: true
    ModifyUser:
      type: "object"
      required:
        - name
        - email
        - password
      properties:
        location_id:
          type: integer
        name:
          type: string
        email:
          type: string
          format: email
        password:
          type: string
          format: password
    User:
      type: "object"
      properties:
        id:
          type: integer
        location_id:
          type: integer
          nullable: true
        profile_picture_file_id:
          type: integer
          nullable: true
        name:
          type: string
        email:
          type: string
          format: email
        password:
          type: string
          format: password
    UserCollection:
      type: "object"
      properties:
        data:
          type: "array"
          items:
            $ref: "#/components/schemas/User"
        links:
          $ref: "#/components/schemas/ApiResourceLinks"
        meta:
          $ref: "#/components/schemas/ApiResourceMeta"

security:
  - app_id: []

tags:
  - name: "Users"
    description: "All user routes"
  - name: "Locations"
    description: "All location routes"

externalDocs:
  description: "External Doc Website"
  url: "https://ayup.agency"
