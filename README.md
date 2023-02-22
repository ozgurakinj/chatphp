# chatphp
a simple chat backend with authorization for bunq job assessment, made with php, slim framework and sqlite


## Users endpoint

### User login
POST /users/{username}, {"username","password"}

### User register
POST /users, {"username","password"}

### Get users list
GET /users

## Chats endpoint

### Retrieve chats for a user
GET /chats, headers=["Authorization"]

### Retrieve messages in a chat
GET /chats/{id}, headers=["Authorization"]

### Send message
POST /chats/message/ headers=["Authorization"], {"to","message"}
