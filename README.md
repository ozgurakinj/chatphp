# chatphp
a simple chat backend for bunq job assessment, made with php slim framework


## Users endpoint

### User login
POST /users/{username} {"username","password"}

### User register
POST /users {"username","password"}

### Get users list
GET /users

## Chats endpoint

### Retrieve chats for a user
GET /chats/ headers=["username"]

### Retrieve messages in a chat
GET /chats/{id}

### Send message
POST /chats/message {"user_id","to","message"}
