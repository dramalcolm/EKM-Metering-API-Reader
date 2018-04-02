# EKM-Metering-API-Reader
This simple class reads all your EKM meter data and makes it available for database saving

The configAPI() function takes an array argument as depicted below:

### Arguments ###
* ***version*** - this is the meter version[currently they have v3 and v4].
* ***api_key*** - the API Key provided by EKM.
* ***count*** - the amount of record you wish to pull between 1 and 1000.
* ***ts*** - this is the timezone eg: America~Jamaica.
* ***format*** - in this case it should be JSON, but they also facilitate HTML.

```php
public function configAPI($args)
{
	isset($args["version"])?$version=$args["version"]:$version="";
	isset($args["api_key"])?$api_key=$args["api_key"]:$api_key="";
	isset($args["count"])?$result_counter=$args["count"]:$result_counter="";
	isset($args["ts"])?$time_zone=$args["ts"]:$time_zone="";
	isset($args["format"])?$format=$args["format"]:$format="json";
}
  ```
