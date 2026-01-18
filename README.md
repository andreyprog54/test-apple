[Русская версия](README.ru.md) | [English version](README.md)

# Test Task: Apple Garden

## Description

This project implements functionality for working with apples according to the test task requirements.

## Technical Stack

- Yii2 Advanced Application Template
- PHP 7.4+
- MySQL 5.7+
- Composer
- Twitter Bootstrap 4

## Requirements

- PHP 7.4 or higher with extensions:
  - PDO
  - PDO MySQL
  - JSON
  - cURL
  - OpenSSL
  - Mbstring
  - Intl
  - XML
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
   ```bash
   git clone [repository-url]
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure the database connection in `common/config/main-local.php`

4. Apply migrations:
   ```bash
   php yii migrate
   ```

## Apple Model Functionality

### States
- `Apple::STATUS_ON_TREE` - On the tree
- `Apple::STATUS_ON_GROUND` - On the ground
- `Apple::STATUS_ROTTEN` - Rotten

### Methods
- `fallToGround()` - Makes the apple fall from the tree
- `eat($percent)` - Eats a percentage of the apple
- `isRotten()` - Checks if the apple is rotten
- `updateRottenState()` - Updates the rotten state based on time

### Attributes
- `color` - Apple color (set randomly on creation)
- `appeared_at` - When the apple appeared (Unix timestamp)
- `fell_at` - When the apple fell (null if still on tree)
- `status` - Current status (on tree/on ground/rotten)
- `eaten_percent` - How much of the apple has been eaten
- `size` - Current size of the apple (1.0 = whole, 0.0 = completely eaten)

## Usage Examples

```php
// Create a new apple
$apple = new Apple();
$apple->color = 'green';
$apple->appeared_at = time();
$apple->save();

// Try to eat an apple on the tree
try {
    $apple->eat(50); // Will throw an exception
} catch (\DomainException $e) {
    echo $e->getMessage();
}

// Make the apple fall
$apple->fallToGround();

// Eat 25% of the apple
$apple->eat(25);

echo $apple->size; // 0.75
```

## Project Structure

```
common/
    config/              # Shared configurations
    mail/                # Email templates
    models/              # Models
    tests/               # Tests for common classes

backend/
    assets/              # Application assets
    config/              # Backend configurations
    controllers/         # Web controllers
    models/              # Backend-specific models
    views/               # View files
    web/                 # Web-accessible files

frontend/
    # Similar structure to backend

console/
    config/              # Console configurations
    controllers/         # Console controllers
    migrations/          # Database migrations

environments/           # Environment configurations
vendor/                 # Composer dependencies
```

## Database Schema

The `apple` table has the following structure:

```sql
CREATE TABLE `apple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color` varchar(32) NOT NULL,
  `appeared_at` int(11) NOT NULL,
  `fell_at` int(11) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 0,
  `eaten_percent` int(11) NOT NULL DEFAULT 0,
  `size` decimal(3,2) NOT NULL DEFAULT 1.00,
  `rotten_at` int(11) DEFAULT NULL,
  `eaten_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

## Configuration

1. Copy `environments/dev/common/config/main-local.php` to `common/config/main-local.php`
2. Update the database connection settings in `common/config/main-local.php`
3. Configure your web server to point to the `backend/web` directory

## Testing

Run the tests with:

```bash
./vendor/bin/codecept run
```

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Author

[Your Name]
