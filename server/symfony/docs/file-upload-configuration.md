# File Upload Configuration Guide

This document explains how to configure file upload limits for the SelfHelp application across different layers: PHP, Apache, and Symfony.

## Overview

File upload limits are controlled at multiple levels, and the most restrictive setting will be the effective limit:

1. **PHP Settings** - Controls file size and post data limits
2. **Apache Settings** - Controls request body size  
3. **Symfony Configuration** - Application-level validation

## PHP Configuration (php.ini)

The primary upload limits are set in your PHP configuration file (`php.ini`):

### Key Settings

```ini
; Maximum allowed size for uploaded files
upload_max_filesize = 100M

; Maximum size of POST data that PHP will accept
; This should be larger than upload_max_filesize to allow multiple files
post_max_size = 1000M

; Maximum number of files that can be uploaded via a single request
max_file_uploads = 20

; Maximum execution time (seconds) - important for large uploads
max_execution_time = 300

; Maximum input time (seconds) - time spent parsing input data
max_input_time = 300

; Memory limit - should be higher than upload limits
memory_limit = 512M
```

### Finding Your php.ini File

- **WAMP**: Usually located at `C:\wamp64\bin\apache\apache{version}\bin\php.ini`
- **XAMPP**: Usually located at `C:\xampp\php\php.ini`
- **Linux**: Often at `/etc/php/{version}/apache2/php.ini` or `/etc/php/{version}/fpm/php.ini`

You can also find it by running: `php --ini`

### Recommended Settings for SelfHelp

```ini
upload_max_filesize = 100M
post_max_size = 1000M
max_file_uploads = 50
max_execution_time = 600
max_input_time = 600
memory_limit = 1024M
```

## Apache Configuration

Apache also has limits that can restrict uploads:

### httpd.conf or .htaccess

```apache
# Maximum request body size (should match or exceed PHP post_max_size)
LimitRequestBody 1048576000  # 1000MB in bytes

# Timeout settings for large uploads
Timeout 600
```

### For WAMP Users

Edit `C:\wamp64\bin\apache\apache{version}\conf\httpd.conf` and add:

```apache
LimitRequestBody 1048576000
```

## Symfony Configuration

The Symfony application doesn't directly control upload sizes but can validate them:

### Framework Configuration (config/packages/framework.yaml)

```yaml
framework:
    http_method_override: true
    # File upload limits are primarily controlled by PHP settings
    # But you can add validation in your services
```

### Application-Level Validation

The `AdminAssetService` includes file validation:

```php
// Maximum file size validation (in addition to PHP limits)
private const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB

// Allowed file extensions
private const ALLOWED_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', // Images
    'pdf', // Documents
    'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', // Videos
    'css', 'js', // Web files
    'zip', 'rar', '7z', // Archives
    'json', // JSON files
    'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' // Office files
];
```

## Testing Upload Limits

### Check Current PHP Settings

Create a PHP file with:

```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
phpinfo();
?>
```

### API Testing

Test upload limits using the assets API:

```bash
# Single file upload
curl -X POST \
  -F "file=@large_file.pdf" \
  -F "folder=test" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/cms-api/v1/admin/assets

# Multiple file upload
curl -X POST \
  -F "files[]=@file1.jpg" \
  -F "files[]=@file2.png" \
  -F "folder=test" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/cms-api/v1/admin/assets
```

## Troubleshooting Upload Issues

### Common Error Messages

1. **"The uploaded file exceeds the upload_max_filesize directive"**
   - Increase `upload_max_filesize` in php.ini

2. **"The uploaded file exceeds the MAX_FILE_SIZE directive"**
   - Check HTML form `MAX_FILE_SIZE` hidden field

3. **"Missing a temporary folder"**
   - Set `upload_tmp_dir` in php.ini or check directory permissions

4. **"Request Entity Too Large" (413 Error)**
   - Increase Apache `LimitRequestBody` setting

5. **"SplFileInfo::getSize(): stat failed for temp file"**
   - This was fixed by moving files immediately in AdminAssetService
   - Avoid calling size methods on temporary files

### Restart Required

After changing PHP or Apache settings, restart your web server:

- **WAMP**: Restart WAMP services
- **XAMPP**: Restart Apache
- **Linux**: `sudo systemctl restart apache2` or `sudo systemctl restart nginx`

## Security Considerations

### File Type Validation

Always validate file types on the server side:

```php
// In AdminAssetService
private const ALLOWED_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
    'pdf', 'mp4', 'avi', 'mov', 'css', 'js'
];
```

### File Size Limits

Set reasonable limits based on your use case:
- **Images**: 10MB should be sufficient for most use cases
- **Videos**: 100MB+ may be needed for video content
- **Documents**: 50MB should handle most document types

### Storage Considerations

Monitor disk space usage as upload limits increase:

```bash
# Check available disk space
df -h

# Monitor upload directory size
du -sh public/uploads/
```

## Performance Tips

1. **Use appropriate limits**: Don't set limits higher than necessary
2. **Monitor uploads**: Log large uploads for analysis
3. **Consider chunked uploads**: For very large files, implement chunked upload
4. **Background processing**: Process large files asynchronously if needed

## Environment-Specific Settings

### Development
```ini
upload_max_filesize = 50M
post_max_size = 500M
max_execution_time = 300
```

### Production
```ini
upload_max_filesize = 100M
post_max_size = 1000M
max_execution_time = 600
memory_limit = 1024M
```

### Testing
```ini
upload_max_filesize = 10M
post_max_size = 100M
max_execution_time = 60
``` 