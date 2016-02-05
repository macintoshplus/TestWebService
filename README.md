# TestWebService

Permet de tester des WebService Soap

# Install

Download Zip or git clone.

open terminal and install dependency
```
$ composer install -o
```

# Configure

create file named `webservices.yml` in root folder.

This is the configuration reference :
```
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
```

# Run


```
php ./TestWebService run [name]
```

If you omit the name, you run all defined tests.

Example : 

```
php ./TestWebService run myWebServiceToTest
```