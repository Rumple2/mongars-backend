# Mongars API - Routes et Payloads JSON

## Auth
POST /api/auth/register
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "phone": "+22501020304",
  "auth_method": "EMAIL"
}
```
POST /api/auth/login
```json
{
  "email": "john@example.com",
  "password": "password"
}
```
POST /api/auth/send-otp
```json
{
  "phone": "+22501020304"
}
```
POST /api/auth/verify-otp
```json
{
  "phone": "+22501020304",
  "otp": "123456"
}
```
POST /api/auth/google
```json
{
  "token": "GOOGLE_TOKEN"
}
```

## Users
GET /api/users/me
PATCH /api/users/me
```json
{
  "name": "Jane Doe"
}
```
POST /api/users/search
```json
{
  "query": "Jane"
}
```

## Couples
GET /api/couples/me
POST /api/couples/break-up
```json
{
  "couple_id": "COUPLE_UUID"
}
```

## Couple Requests
GET /api/couple-requests/received
GET /api/couple-requests/sent
POST /api/couple-requests/send
```json
{
  "user_id": "USER_UUID",
  "candidate_id": "CANDIDATE_UUID"
}
```
POST /api/couple-requests/{request_id}/respond
```json
{
  "status": "ACCEPTED"
}
```

## Search
POST /api/search/user
```json
{
  "user_id": "USER_UUID",
  "search_term": "Jane"
}
```
GET /api/search/history

## Stats
GET /api/stats/user
GET /api/stats/global
GET /api/stats/profile-viewers

## Subscriptions
GET /api/subscriptions/me
POST /api/subscriptions/subscribe
```json
{
  "user_id": "USER_UUID",
  "type": "PREMIUM"
}
```

## Notifications
GET /api/notifications/unread/count
POST /api/notifications/{notification_id}/mark-as-read
