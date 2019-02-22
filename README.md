# TestWebService

Allow check WebService Soap and HTTP (REST API) and store result into File or influxDB.

[![Build Status](https://travis-ci.org/macintoshplus/TestWebService.svg?branch=master)](https://travis-ci.org/macintoshplus/TestWebService)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](https://github.com/macintoshplus/TestWebService/blob/master/LICENSE)

# Requirements

* PHP 7.1+
* PHP Curl Extension (with the CA-Bundle INI paramater defined)
* PHP OpenSSL Extension (for HTTPS support)
* PHP SOAP Extension (for SOAP support)
* PHP XML Extension (for SOAP support)

# Install

Download Zip or git clone.

open terminal and install dependency
```
$ composer install -o
```

# Configure

create file named `webservices.yml` in root folder.

This is the configuration reference :


```yaml
webservices:
    myWebServiceToTest: 
        class: Mactronique\TestWs\WebServices\WsSoap # You can write your class for specified tests.
        config: 
            # If you use the WsSoap class test, set this key with 'methodCall' value for use the '__soapCall' method in client. Set other value for call the specified function below.
            methodCall: byFunctionName
            env: 
                # Set here all environement URL. For WsSoap, set the WSDL URL.
                prod: 'http://host_name/wsForMe.wsdl'
            functions:
                # Set here the function do call over the SOAP web service
                wsFunctionName:
                    parameters1: value1
                    parameters2: value2
            datas:
                # Set here the datas for HTTP WebService
                method: GET              # Or POST, PUT...
                mime: application/json   # Query mime
                datas: ~                 # Query body
                authorization: ~         # Authorisation Header Content
            response:                    # The data for evaluate the response.
                http_code: 200           # The HTTP Code need.
                server_header: SRV       # The name of header with the value can identify the server.
        # Exemple of persistance configuration for InfluxDB
        storage:
            type: InfluxDB
            config:                      # Set the config for the driver
                host: 127.0.0.1          # Server name or IP
                port: 8086               # The TCP Port
                username: user_write     # The username
                password: *****          # The password for the user database
                ssl: false               # The connexion user SSL
                verifySSL: true          # Check Certificat
                timeout: 1.0             # Connection time out
                database: testws         # Database name
        # Exemple of persistance configuration for File
        storage:
            type: File
            config:
                file: file.json          # Location for the destination file.
```

# Run


```bash
php ./TestWebService run [name]
```

If you omit the name, you run all defined tests.

Example : 

```bash
php ./TestWebService run myWebServiceToTest
```

# Retreive data from InfluxDb

You can use [Grafana](https://grafana.com/) for display datas stored into InfluxDB database.


# Contribute

If you need help or if you have a bug or error, open an issue.

If you want a new feature or change a feature, please open an issue or submit a pull request.

# Feeadback

I'm need feedback (good or bad) for your usage of this tool. You can send me your feed back by opening an issue.
