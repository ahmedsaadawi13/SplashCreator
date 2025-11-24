# SplashCreator

**AI-Powered Multi-Tenant SaaS Platform for Content Creation & Social Media Management**

SplashCreator is a comprehensive PHP-based SaaS platform that enables agencies, marketing teams, and content creators to generate AI-powered content (text, images, videos), manage content libraries, schedule posts across multiple social media platforms, and analyze performance metrics.

## Features

### 🤖 AI Content Generation
- **Text Generation**: Posts, captions, tweets, scripts, ad copy, blog outlines
- **Image Generation**: Thumbnails, social graphics, custom sizes
- **Video Generation**: Short clips with AI voiceover and subtitles (simulated)
- Template library for quick content creation
- Brand kit integration for consistent styling

### 📅 Content Management & Scheduling
- Centralized content library (text, image, video)
- Content calendar with visual timeline
- Schedule posts across multiple platforms
- Post now or schedule for future
- Bulk scheduling capabilities
- Team collaboration with comments

### 🌐 Social Media Integration (Simulated)
- **Supported Platforms**: Instagram, Facebook, Twitter/X, TikTok, LinkedIn, YouTube Shorts
- Multi-account management per platform
- Simulated OAuth integration
- Auto-posting engine with cron scheduler
- Post status tracking (scheduled, posted, failed)

### 📊 Analytics & Reporting
- Platform-wise performance metrics
- Content performance tracking (impressions, likes, comments, shares, clicks)
- Top performing content analysis
- Engagement rate calculations
- Dashboard overview with key metrics

### 🎨 Brand Management
- Create multiple brand kits
- Define brand colors, fonts, and voice tone
- Apply brand guidelines to AI-generated content
- Logo and asset management

### 👥 Multi-Tenant Architecture
- Complete tenant isolation
- Role-based access control (Platform Admin, Tenant Admin, Content Creator, Social Manager, Viewer)
- Subscription plan management
- Usage quota tracking and enforcement
- Per-tenant API keys

### 🔒 Security & Compliance
- Password hashing (bcrypt)
- CSRF protection
- SQL injection prevention (PDO prepared statements)
- XSS protection (output escaping)
- Rate limiting
- Secure session management
- Activity logging

### 🚀 REST API
- Full REST API for external integrations
- API key authentication
- Endpoints for text, image, video generation
- Content management API
- Scheduling API

## Technology Stack

- **Backend**: PHP 7.0+ (fully compatible)
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Architecture**: Custom lightweight MVC
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **No Frameworks**: No Laravel, Symfony, or JavaScript frameworks

## Installation

### Prerequisites

- PHP 7.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache or Nginx web server
- mod_rewrite enabled (Apache)

### Step 1: Clone Repository

```bash
git clone https://github.com/ahmedsaadawi13/splashcreator.git
cd splashcreator
```

### Step 2: Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and update database credentials:

```env
DB_HOST=localhost
DB_NAME=splashcreator
DB_USER=root
DB_PASS=your_password
BASE_URL=http://localhost/splashcreator
```

### Step 3: Import Database

```bash
mysql -u root -p < database.sql
```

This will create the database, all tables, and seed demo data.

### Step 4: Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
chmod -R 755 storage/logs/
```

### Step 5: Configure Web Server

#### Apache

Create a virtual host or point your document root to `/path/to/splashcreator/public`

Example Apache configuration:

```apache
<VirtualHost *:80>
    ServerName splashcreator.local
    DocumentRoot /var/www/splashcreator/public

    <Directory /var/www/splashcreator/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashcreator_error.log
    CustomLog ${APACHE_LOG_DIR}/splashcreator_access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name splashcreator.local;
    root /var/www/splashcreator/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 6: Set Up Cron Job

Add this to your crontab to enable automatic post scheduling:

```bash
* * * * * php /path/to/splashcreator/cron_scheduler.php >> /path/to/splashcreator/storage/logs/cron.log 2>&1
```

This runs the scheduler every minute to process due posts.

## Demo Credentials

After importing the database, you can log in with these demo accounts:

**Platform Administrator:**
- Email: `admin@splashcreator.com`
- Password: `password`

**Tenant Admin (Demo Agency):**
- Email: `admin@demo.com`
- Password: `password`

**Content Creator:**
- Email: `creator@demo.com`
- Password: `password`

**Social Manager:**
- Email: `social@demo.com`
- Password: `password`

## Usage Guide

### AI Text Generation

1. Navigate to **AI Text** from the menu
2. Enter your prompt (e.g., "Write an Instagram caption about morning coffee")
3. Select tone (professional, casual, friendly)
4. Click **Generate**
5. Save to content library or copy to clipboard

### AI Image Generation

1. Navigate to **AI Image**
2. Enter image description
3. Select style (realistic, abstract, artistic)
4. Choose dimensions
5. Click **Generate**
6. Save to content library

### AI Video Generation

1. Navigate to **AI Video**
2. Enter your video script
3. Select resolution
4. Click **Generate**
5. Video will be created with simulated voiceover
6. Save to content library

### Content Management

1. Navigate to **Content**
2. View all your created content
3. Filter by type (text, image, video)
4. Edit, delete, or schedule content
5. Add comments for team collaboration

### Scheduling Posts

#### Method 1: From Content Library

1. Go to **Content** and select an item
2. Click **Schedule Post**
3. Select social account and platform
4. Choose date/time
5. Click **Schedule**

#### Method 2: From Schedule Page

1. Navigate to **Schedule**
2. Click **Create Schedule**
3. Select content item
4. Select social account
5. Choose date/time
6. Click **Schedule**

#### Post Immediately

1. Select content
2. Click **Post Now**
3. Choose platform
4. Content will be posted immediately

### Social Media Management

1. Navigate to **Social**
2. Click **Connect Account**
3. Select platform and enter username
4. Account will be connected (simulated)
5. View account stats and posting history

### Analytics

1. Navigate to **Analytics**
2. View overall performance metrics
3. Filter by platform
4. Analyze content performance
5. Export reports (coming soon)

### Brand Kits

1. Navigate to **Brand** (Tenant Admin only)
2. Click **Create Brand Kit**
3. Enter brand name
4. Add brand colors (hex codes)
5. Specify fonts
6. Choose voice tone
7. Save brand kit
8. Apply to AI generations

## REST API Documentation

### Authentication

All API requests require an API key in the header:

```
X-API-KEY: your_api_key_here
```

Get your API key from the database `tenant_api_keys` table.

### Endpoints

#### Generate Text

```http
POST /api/text/generate
Content-Type: application/json

{
  "prompt": "Write a tweet about productivity",
  "tone": "professional"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 123,
    "text": "Generated text content...",
    "tokens": 87,
    "model": "gpt-4"
  }
}
```

#### Generate Image

```http
POST /api/image/generate
Content-Type: application/json

{
  "prompt": "Modern office workspace",
  "style": "realistic",
  "width": 1024,
  "height": 1024
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 456,
    "image_path": "storage/uploads/1/generated/image_001.jpg",
    "width": 1024,
    "height": 1024
  }
}
```

#### Generate Video

```http
POST /api/video/generate
Content-Type: application/json

{
  "script_text": "Welcome to our platform...",
  "resolution": "1920x1080"
}
```

#### Create Content

```http
POST /api/content/create
Content-Type: application/json

{
  "title": "My Content",
  "type": "text",
  "content_text": "Content goes here..."
}
```

#### Schedule Post

```http
POST /api/schedule/create
Content-Type: application/json

{
  "content_id": 123,
  "social_account_id": 456,
  "scheduled_time": "2024-12-25 10:00:00"
}
```

#### Get Content Library

```http
GET /api/content?type=text&limit=50
```

### Error Responses

```json
{
  "status": "error",
  "message": "Error description",
  "code": 400
}
```

## Testing

Run the provided test suite:

```bash
# Test database connection
php tests/test_db_connection.php

# Test AI text generation
php tests/test_ai_text_generation.php

# Test AI image generation
php tests/test_ai_image_generation.php

# Test AI video generation
php tests/test_ai_video_generation.php

# Test scheduler functionality
php tests/test_scheduler.php

# Test social posting
php tests/test_social_posting.php

# Test tenant isolation
php tests/test_tenant_isolation.php

# Test API authorization
php tests/test_api_authorization.php
```

## Project Structure

```
splashcreator/
├── app/
│   ├── controllers/       # All controllers
│   ├── models/           # Database models
│   ├── views/            # HTML templates
│   ├── core/             # Core MVC classes
│   └── helpers/          # Helper classes
├── config/               # Configuration files
├── public/               # Web root
│   ├── css/
│   ├── js/
│   └── index.php        # Entry point
├── storage/
│   ├── uploads/         # User uploads
│   └── logs/            # Application logs
├── tests/               # Test files
├── database.sql         # Database schema + seed
├── cron_scheduler.php   # Cron job script
├── .env.example         # Environment template
└── README.md
```

## Subscription Plans

Three tiers included in seed data:

### Starter - $29/month
- 100 text generations
- 50 image generations
- 10 video generations
- 100 scheduled posts
- 3 social accounts
- Basic analytics

### Professional - $79/month
- 500 text generations
- 250 image generations
- 50 video generations
- 500 scheduled posts
- 10 social accounts
- Team collaboration
- Advanced analytics

### Agency - $199/month
- 2,000 text generations
- 1,000 image generations
- 200 video generations
- 2,000 scheduled posts
- 50 social accounts
- White label
- Priority support

## Security Considerations

✅ **Implemented:**
- Password hashing with bcrypt
- CSRF token validation
- PDO prepared statements (SQL injection prevention)
- Output escaping (XSS prevention)
- Session regeneration on login
- Tenant isolation in queries
- Rate limiting on API endpoints
- Activity logging with IP tracking

⚠️ **Production Recommendations:**
- Enable HTTPS/SSL
- Implement API rate limiting per tenant
- Add email verification
- Set up automated backups
- Configure proper error logging
- Implement file upload virus scanning
- Add two-factor authentication
- Set up monitoring and alerts

## Scalability Considerations

**Current Implementation:**
- Single server architecture
- Synchronous processing
- File-based uploads

**For Scale:**
- Implement job queues (Redis + workers)
- Move to cloud storage (S3, CloudFlare R2)
- Add CDN for static assets
- Implement caching (Redis/Memcached)
- Database read replicas
- Microservices for AI generation
- Load balancing
- Horizontal scaling

## Troubleshooting

### Database Connection Failed
- Verify credentials in `.env`
- Ensure MySQL service is running
- Check database exists

### 404 Errors
- Verify mod_rewrite is enabled (Apache)
- Check `.htaccess` exists in `public/`
- Verify document root points to `public/`

### Permissions Errors
- Ensure `storage/` is writable: `chmod -R 755 storage/`
- Check web server user owns files

### Cron Not Running
- Verify cron job is added to crontab
- Check cron log: `tail -f storage/logs/cron.log`
- Test manually: `php cron_scheduler.php`

## License

MIT License - See LICENSE file for details

## Support

For issues and feature requests, please open an issue on GitHub.

## Credits

Built with ❤️ as a demonstration of custom PHP MVC architecture for multi-tenant SaaS applications.

---

**Note**: This is a demonstration platform with simulated AI generation and social media posting. For production use, integrate real AI APIs (OpenAI, Stable Diffusion, etc.) and social media OAuth flows.
