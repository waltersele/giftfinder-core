# Giftfinder Core Plugin

WordPress plugin for managing AI-generated gift recommendations and affiliate product integrations.

## Features

- **REST API Integration**: Autonomous product ingestion via `/wp-json/giftia/v1/ingest`
- **Product Management**: Store products with AI scores and price history
- **Affiliate Integration**: Amazon, Awin, and TopDrop affiliate links
- **Custom Post Type**: `gf_gift` for gift products
- **Price Tracking**: Historical price logs for price analysis

## Installation

1. Copy plugin to `wp-content/plugins/giftfinder-core/`
2. Activate plugin in WordPress admin
3. Configure API token in WordPress options:
   ```php
   update_option('gf_api_token', 'your-token-here');
   ```

## API Endpoints

### POST `/wp-json/giftia/v1/ingest`

Ingest products from Hunter.py scraper.

**Required Headers:**
```
X-GIFTIA-TOKEN: your-api-token
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Product Title",
  "description": "Product description",
  "price": 99.99,
  "currency": "EUR",
  "image_url": "https://example.com/image.jpg",
  "affiliate_url": "https://amazon.es/...",
  "source": "amazon",
  "category": "Tech",
  "gift_score": 8.5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product created",
  "post_id": 12345
}
```

## Database Tables

- `gf_products_ai`: Product data from Hunter
- `gf_affiliate_offers`: Affiliate link tracking
- `gf_price_logs`: Historical price data

## Configuration

Set via WordPress options or environment variables:

- `gf_api_token`: API token for ingest endpoint
- `gf_amazon_tag`: Amazon affiliate tag
- `gf_gemini_key`: Google Gemini API key (for content generation)

## Files

- `giftfinder-core.php`: Main plugin file with REST API
- `admin-settings.php`: WordPress admin settings page
- `api-ingest.php`: Legacy endpoint (deprecated, use REST API)
- `frontend-ui.php`: Frontend gift display component
- `install.php`: Database table installation

## Development

**Requirements:**
- WordPress 5.0+
- PHP 7.4+
- cURL support

**Testing:**
```bash
curl -X POST https://giftia.es/wp-json/giftia/v1/ingest \
  -H "X-GIFTIA-TOKEN: your-token" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","price":10.00,"source":"amazon"}'
```

## Related

- **Hunter**: Autonomous Amazon scraper - [giftia-hunter](https://github.com/waltersele/giftia-hunter)
- **Website**: https://giftia.es

## License

Private
