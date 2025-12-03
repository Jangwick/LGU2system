# LRMS Notification Integration API

## Overview

The LRMS Notification system provides webhook endpoints for external integrated modules to send notifications, file alerts, and messages to the LRMS system.

## Authentication

All API requests require an API key to be included in the request header.

```
X-API-Key: your_api_key_here
```

### Getting an API Key

API keys are generated for each integration module. Contact the system administrator to obtain an API key for your integration.

## Endpoints

### Base URL
```
POST /modules/notifications/api/webhook.php
```

---

## 1. Send Notification

Send a general notification to users.

**Request:**
```json
{
  "action": "send_notification",
  "title": "New Document Available",
  "message": "A new resolution has been filed for review",
  "type": "integration",
  "priority": "normal",
  "target_user_id": null,
  "target_role": "administrator",
  "source_id": "external_ref_123",
  "data": {
    "document_id": "DOC-2025-001",
    "link": "https://external-system.com/doc/123"
  },
  "expires_at": "2025-12-31 23:59:59"
}
```

**Parameters:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| action | string | Yes | Must be "send_notification" |
| title | string | Yes | Notification title (max 255 chars) |
| message | string | Yes | Notification message body |
| type | string | No | Type: file, message, alert, integration, system (default: integration) |
| priority | string | No | Priority: low, normal, high, urgent (default: normal) |
| target_user_id | int | No | Send to specific user ID (null for broadcast) |
| target_role | string | No | Send to all users with this role |
| source_id | string | No | External reference ID for tracking |
| data | object | No | Additional JSON data |
| expires_at | string | No | Expiration datetime (Y-m-d H:i:s) |

**Response:**
```json
{
  "success": true,
  "notification_id": 123,
  "message": "Notification broadcast to all users"
}
```

---

## 2. Send File Notification

Notify users about an incoming file from an external system.

**Request:**
```json
{
  "action": "send_file",
  "title": "New Resolution Received",
  "message": "Resolution No. 2025-001 has been submitted from Committee Management",
  "file_name": "resolution_2025_001.pdf",
  "file_url": "https://committee-system.gov.ph/files/resolution.pdf",
  "file_type": "resolution",
  "file_size": 1024000,
  "priority": "high",
  "target_role": "officer",
  "data": {
    "committee": "Finance Committee",
    "session_date": "2025-01-15"
  }
}
```

**Parameters:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| action | string | Yes | Must be "send_file" |
| title | string | Yes | Notification title |
| message | string | Yes | File description |
| file_name | string | No | Name of the file |
| file_url | string | No | URL to access/download the file |
| file_type | string | No | Type: resolution, ordinance, agenda, committee, hearing |
| file_size | int | No | File size in bytes |
| priority | string | No | Priority level |
| target_role | string | No | Target user role |
| data | object | No | Additional metadata |

---

## 3. Send Message

Send a message notification (e.g., from public consultations).

**Request:**
```json
{
  "action": "send_message",
  "title": "Public Feedback Received",
  "message": "A citizen has submitted feedback on Ordinance No. 2025-005",
  "sender_name": "Juan Dela Cruz",
  "sender_email": "juan@example.com",
  "priority": "normal",
  "target_role": "officer",
  "data": {
    "feedback_type": "suggestion",
    "ordinance_number": "2025-005"
  }
}
```

**Parameters:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| action | string | Yes | Must be "send_message" |
| title | string | Yes | Message notification title |
| message | string | Yes | Message content |
| sender_name | string | No | Name of the message sender |
| sender_email | string | No | Email of the sender |
| reply_to | string | No | Reply-to address/URL |
| target_role | string | No | Target user role |

---

## 4. Health Check (Ping)

Test the webhook connection.

**Request:**
```json
{
  "action": "ping"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Integration webhook is active",
  "module": "committee-management",
  "timestamp": "2025-12-04 10:30:00"
}
```

---

## Target Options

### By User ID
Send to a specific user:
```json
{
  "target_user_id": 5
}
```

### By Role
Send to all users with a specific role:
```json
{
  "target_role": "administrator"
}
```

Available roles: `administrator`, `officer`, `viewer`

### Broadcast
Send to all users (omit both target_user_id and target_role):
```json
{
  "target_user_id": null
}
```

---

## Priority Levels

| Priority | Description | Visual Indicator |
|----------|-------------|------------------|
| low | Non-urgent informational | Gray badge |
| normal | Standard notification | Blue badge |
| high | Important, needs attention | Orange badge |
| urgent | Critical, immediate action required | Red badge |

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "error": "Invalid or inactive API key"
}
```

### 400 Bad Request
```json
{
  "success": false,
  "error": "Title and message are required"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "error": "Permission denied: send_file"
}
```

---

## Code Examples

### PHP
```php
<?php
$apiKey = 'your_api_key';
$webhookUrl = 'https://lrms.valenzuela.gov.ph/modules/notifications/api/webhook.php';

$data = [
    'action' => 'send_notification',
    'title' => 'New Document',
    'message' => 'A new ordinance has been submitted',
    'priority' => 'high',
    'target_role' => 'administrator'
];

$ch = curl_init($webhookUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
```

### JavaScript (Node.js)
```javascript
const axios = require('axios');

const webhookUrl = 'https://lrms.valenzuela.gov.ph/modules/notifications/api/webhook.php';
const apiKey = 'your_api_key';

async function sendNotification() {
    try {
        const response = await axios.post(webhookUrl, {
            action: 'send_file',
            title: 'New Resolution',
            message: 'Resolution No. 2025-001 submitted',
            file_name: 'resolution.pdf',
            file_type: 'resolution',
            target_role: 'officer'
        }, {
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': apiKey
            }
        });
        
        console.log(response.data);
    } catch (error) {
        console.error(error.response.data);
    }
}

sendNotification();
```

### Python
```python
import requests

webhook_url = 'https://lrms.valenzuela.gov.ph/modules/notifications/api/webhook.php'
api_key = 'your_api_key'

data = {
    'action': 'send_message',
    'title': 'Public Consultation Feedback',
    'message': 'New feedback received on proposed ordinance',
    'sender_name': 'Juan Dela Cruz',
    'target_role': 'officer'
}

response = requests.post(
    webhook_url,
    json=data,
    headers={
        'Content-Type': 'application/json',
        'X-API-Key': api_key
    }
)

print(response.json())
```

---

## Integration Modules

The following integration modules are pre-configured:

| Module | Permissions |
|--------|-------------|
| Committee Management | send_notification, send_file |
| Public Hearings | send_notification, send_file |
| Archives | send_notification, send_file |
| Consultations | send_notification, send_message |
| Research | send_notification, send_file |

---

## Support

For API key requests or technical support, contact:
- Email: it@valenzuela.gov.ph
- System Administrator: LRMS Admin Portal
