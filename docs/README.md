# HumHub Antivirus Module

The **Antivirus Module** is a security module designed to protect your HumHub installation by allowing admins to manually scanning uploaded files for potential threats. It checks files for known dangerous extensions, MIME types, and virus signatures to help safeguard your system from malicious uploads.

## Features

- **Manual Scanning**: Admins can manually scan files uploaded to the HumHub platform.
- **Virus Signatures**: Detects files with known virus signatures, including the **EICAR test virus**.
- **Dangerous File Types**: Scans files for dangerous extensions and MIME types to prevent malware uploads.
- **Customizable**: Extend and update virus signatures to improve detection.

## Installation

1. Download or clone the module from the repository.
2. Copy the module folder into your HumHub `modules` directory.
3. Go to the **Administration > Modules** section.
4. Enable the **Antivirus** module.

## Configuration

After enabling the module, you can configure it by navigating to the **Antivirus Settings** page for the Antivirus module in the admin panel.

### Virus Signatures

The module includes the EICAR test virus signature by default, but you can extend this list with additional known virus signatures.

#### Configuration Example:
In `protected/config/common.php` you can configure `virusSignatures` like so;
```php
return [
    'modules' => [
        'antivirus' => [
            'virusSignatures' => [
                'EICAR' => '58354F2150254041505B345C505A58353428505E2937434329377D24454943415' . 
                           '22D5354414E444152442D414E544956495255532D544553542D46494C452124' . 
                           '48202B',
                // Add additional virus signatures here if needed
            ],
        ],
    ],
];
```
