
# Website Project

A course project for developing a PHP-based website using the WAMP server.

## Requirements

- Windows operating system
- WAMP Server
- Modern web browser

## Installation & Setup

1. **Install WAMP Server**
   - Download and install WAMP from the official website
   - Ensure all services start correctly (Apache, MySQL)

2. **Deploy Project Files**
   - Copy all project files to:  
     `\wamp64\www\example\`

3. **Configure Database Connection**
   - Open `config.php` in the project root
   - Verify database credentials match your local MySQL setup

4. **Generate Test Data (Optional)**
   - Use this Colab notebook to populate the database with fake data:  
     [Generate Fake Data](https://colab.research.google.com/drive/14ldz4iePN9nV9PEA3vk5MO4cHujecM5v?usp=sharing)

5. **Launch the Application**
   - Start WAMP services
   - Open your browser and navigate to:  
     `http://localhost/example/index.php`

## Project Structure

```
example/
├── index.php          # Entry point
├── config.php         # Database configuration
├── ...          # other components
├── demo/            # separate code part to demonstrate why transactions are important
└── ссп_отчет.pdf      # Project report (documentation)
```

## Documentation

For detailed technical specifications, architecture decisions, and implementation notes, refer to:  
**`ссп_отчет.pdf`** (included in the project root)



## Notes

- This project is intended for **local development only**
- Default MySQL credentials for WAMP: user `root`, empty password
- Make sure PHP extensions required by the project are enabled in `php.ini`



