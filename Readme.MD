# OSPOS CSV Upload Automation Script

This script automates the process of uploading CSV files to the Open Source Point of Sale (OSPOS) system. It supports importing data into both the **Items** and **Customers** sections, using Selenium for web automation.

## Features
✅ Supports both **Items** and **Customers** CSV uploads  
✅ Securely stores credentials and settings using **keyring**  
✅ Automatically navigates and imports CSV files one by one  
✅ Allows users to set and store OSPOS URL, ChromeDriver path, and CSV directory  
✅ Handles failed uploads and retries them  
✅ Default credentials and paths can be used or overridden  

## Requirements
- **Python 3.8 or later**
- **Google Chrome** (latest version recommended)
- **ChromeDriver** (matching your Chrome version)
- Required Python modules:
  ```sh
  pip install selenium keyring
  ```
- OSPOS running on any of the following server environments:
  - **XAMPP** (Windows, Linux, Mac)
  - **WAMP** (Windows-based stack)
  - **LAMP** (Linux-based stack)
  - **MAMP** (Mac-based stack)
  - **Docker-based OSPOS setup**

## How to Use
1. **Download the CSV template from the CSV Import option in OSPOS** before preparing your CSV files.
2. **Edit the downloaded CSV file** using a **spreadsheet editor (Excel, Google Sheets, LibreOffice Calc)** or **a text editor (Notepad, VS Code, etc.)** to input your data.
3. **Move the edited CSV file to a dedicated folder** where it will be uploaded from.
4. **Clone the repository**
   ```sh
   git clone https://github.com/ShadowXByte/ospos-csv-automation.git
   cd ospos-csv-automation
   ```
5. **Run the script**
   ```sh
   python upload_ospos.py
   ```
6. **Follow the prompts** to enter your OSPOS credentials, URL, and CSV directory.
7. Select whether you want to upload **Items** or **Customers** CSV files.
8. The script will automatically process and upload all CSV files.

## Configuration
- The script will **store credentials** (username, password, OSPOS URL) securely using `keyring`.
- Default values can be modified in the script.
- Users can re-enter credentials at any time.

## Important Update: Selenium 4 Compatibility
- **Starting from Selenium 4, `executable_path` is no longer used.** Instead, ChromeDriver should be initialized using:
  ```python
  from selenium.webdriver.chrome.service import Service
  
  service = Service(CHROMEDRIVER_PATH)
  driver = webdriver.Chrome(service=service, options=options)
  ```
  This script is updated to be fully compatible with **Selenium 4**.

## Troubleshooting
- **OSPOS URL Not Working?** Ensure OSPOS is running and the correct URL is provided.
- **ChromeDriver Error?** Check if your ChromeDriver version matches your installed Chrome version.
- **Server Not Running?** Ensure your server environment (XAMPP, WAMP, LAMP, MAMP, Docker) is properly configured.

## License
This project is licensed under the **MIT License**. Feel free to modify and contribute!

## Contributions
Pull requests are welcome! If you improve the script or add features, consider sharing them with the community.

---

### Maintained by [ShadowXByte](https://github.com/ShadowXByte)

