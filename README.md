# TestWebService

Allow check WebService Soap and HTTP and store result into File on influxDB.

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
            	# Set here the function do call over the web service
            	wsFunctionName:
            		parameters1: value1
            		parameters2: value2
            response: # The data for evaluate the response.
                http_code: 200 # The HTTP Code need.
                server_header: SRV # The name of header with the value can identify the server.
        # Exemple of persistance configuration for InfluxDB
        storage:
            type: InfluxDB
            config: # Set the config for the driver
                host: 127.0.0.1
                database: testws
        # Exemple of persistance configuration for File
        storage:
            type: File
            config:
                file: file.json # Location for the destination file.
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