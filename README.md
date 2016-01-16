#blAPI
###Battlelog API - simple, one-class API ready to help you get data from Battlelog. 

It not only gets the data directly from Battlelog, but blAPI can also change Battlelog raw data to ready to use values!
![Extra stuff!](http://i.imgur.com/e62Ho8P.jpg)

###How to use blAPI?

There are 4 public methods at the momement (and these are the only you should worry about) and (obviously) constructor:

######constructor
Constructor requires only one argument - $game. It must be a shorthand of one of supported games or "unknown" if you don't want to load  all of config files now. 
Supported games are: 

 * Battlefield 4 - "bf4"
 * Battlefield Hardline - "bfh"
 
 Other arguments are: $blapi_light = false, $logs = false. Set first one to true if you don't want to use the feature shown on picture on the top of the page. blAPI will load then only crucial (game)_config_map file. Set second one to true if you want to enable logging events to text file.

Events are logged to a text file blapi_log.txt. 
 
 Basic initialization:
 ```php
 $blapi = new blAPI('bf4');
 $blapi = new blAPI("unknown");
 ```
 More arguments:
 ```php
 $blapi = new blAPI("bfh", true, true);
 $blapi = new blAPI("bf4", false, true);
 ```
 
 **Summary of __construct($game, $blapi_light = false, $logs = false)**
 
 Argument     | Data
------------- | -------------
$game*  | String, supported values: bf4, bfh
$blapi_light | False by default, if true, blAPI will use only one, crucial config file
$logs | False by default, if true, blAPI will log stuff to a text file

######setGame($game)
This method allows you to set or change the game you want to recieve the data from. This can be set when constructing a new object too. So you can use one of two ways to set the game: 
  ```php
  $blapi = new blAPI('bf4');
  ```
Now you don't have to run setGame() method, unless you want to change the game.
Second way:
  ```php
  $blapi = new blAPI('unknown');
  $blapi->setGame('bfh');
  ```
You can also change the game to other.
  ```php
  $blapi = new blAPI('bf4');
  $blapi->setGame('bfh');
  ```
**Summary of setGame($game)**

Argument     | Data
------------- | -------------
$game*  | String, supported values: bf4, bfh
$blapi_light | False by default, if true, blAPI will use only one, crucial config file

######getServerData($server_url, $json = false, $human_friendly = false)

This method returns an array or JSON object (based on $json value) with data about one multiplayer server. There are 3 arguments, only first is obligatory - it's $server_url. This variable must contain a full URL of server, for example:
```
http://battlelog.battlefield.com/bf4/servers/show/pc/ca9bab03-2c3d-4d0e-a332-8ac3a56a80c9/E4GL-CQL-64-Slots-24-7-Large-Scale-Battles/
```
You can use this method in 2 ways.

First - you're passing only $server_url variable. Method will return associative array with raw data (that means output will look like on the left side of picture at the beggining of this readme. I know it's JSON in the picture.)
```php
$server = 'http://battlelog.battlefield.com/bf4/servers/show/pc/ca9bab03-2c3d-4d0e-a332-8ac3a56a80c9/E4GL-CQL-64-Slots-24-7-Large-Scale-Battles/';
$data = $blapi->getServerData($server);
```

Second - you're passing $server_url and additional arguments. These two arguments are $json (can be either true or false) and $human_friendly (also true or false). For example, if you want to get a JSON object with ready to use values (like on the right side of picture):
```php
  $server = 'http://battlelog.battlefield.com/bf4/servers/show/pc/ca9bab03-2c3d-4d0e-a332-8ac3a56a80c9/E4GL-CQL-64-Slots-24-7-Large-Scale-Battles/';
  $data = $blapi->getServerData($server, true, true);
```
**Summary of getServerData($server_url, $json = false, $human_friendly = false)**

Argument     | Data
------------- | -------------
$server_url* | Contains string with full server URL
$json | *False by default.* Can be *true* or *false*. If true, method will return JSON object. If false - associative array
$human_friendly | *False by default.* Can be *true* or *false*. If true, method will return JSON/array with correct names of maps, correct preset names - like on the right side of picture. E.g. instead of XP_002 there will be "Nansha Strike".

######getPlayerData($player_id, $json = false, $human_friendly = false, $big = false)

Returns an array or JSON object (based on $json value) with data about one player (actually one soldier). 4 arguments, only first is obligatory - $player_id. Must contain soldier ID, to get this number go to somebody's Battlelog profile. Then choose a soldier (or agent in BFH) - bam, we've got the URL.
```
http://battlelog.battlefield.com/bf4/soldier/ArekTheMLGPro/stats/887022216/pc/
887022216 - this is our ID
```
Basic way of calling this method:

```php
$id = "887022216";
$data = $blapi->getPlayerData($id);
```
Method will return associative array with data about player. 

If you want JSON, make it $human_friendly or get bigger chunk of data (that's other "Battlelog API" url, it returns huge array, around 2000 lines, it has also data about emblem), then you have to set correct variables to true.

One word about this way to get a bigger chunk of data. It is returned by http://battlelog.battlefield.com/bf4/warsawoverviewpopulate/{ID}/1/ URL, check it by yourself. This callback has a lot more information, but mainly about internal paths to images, data about emblem. Smaller and faster one, http://battlelog.battlefield.com/bf4/warsawdetailedstatspopulate/{ID}/1/ has enough info about player and it will be enough in 99% of cases. Keep in mind that $human_friendly variable **doesn't work with this huge callback**. It's possible to use this only with smaller array.

Let's say that I want to get a JSON object, with "human" values (e.g. instead of 2048 there will be Commander):

```php 
$id = 887022216; //can be also '887022216' - that doesn't matter
$data = $blapi->getPlayerData($id, true, true);
```
**Summary of getPlayerData ($player_id, $json = false, $human_friendly = false, $big = false)**

Argument     | Data
------------- | -------------
$player_id* | ID of soldier/agent (check above to see which ID and how to get this ID)
$json | *False by default.* Can be *true* or *false*. If true, method will return JSON object. If false - associative array
$human_friendly | *False by default.* Can be *true* or *false*. If true, method will return JSON/array with correct names of maps, correct preset names - like on the right side of picture. E.g. instead of 34359738368 there will be "Chain Link".
$big = false - even if true in $human_friendly is passed, method will return raw data, like on the left side of the picture at the beginning. Look above for more information about $big

######getBattleReport($report_id, $json = false, $human_friendly = false)

Returns array or JSON object (based on $json variable) with data about one battlereport. There are 3 arguments, only first is obligatory. You probably know two others, as you probably read whole of this readme. In fact, these two will cause the same things as in getPlayerData() and getServerData().

$report_id must contain an ID of the report. This id is hidden in battlereport URL. Here is an example URL:
```
http://battlelog.battlefield.com/bf4/battlereport/show/1/561823230545492992/887022216/
561823230545492992 - this is our battlereport ID. The second number (887022216) is ID of OUR SOLDIER.
```
We know the stuff, here's basic way of calling this method:
```php
$id = 561823230545492992; //can be also '561823230545492992'
$data = $blapi->getBattleReport($id);
```
Calling this method with more arguments - with human-friendly output in JSON:
```php 
$id = 561823230545492992;
$data = $blapi->getBattleReport($id, true, true);
```
**Summary of getBattleReport($report_id, $json = false, $human_friendly = false)**

Argument     | Data
------------- | -------------
$report_id* | Must contain an ID of battlereport
$json | *False by default.* Can be *true* or *false*. If true, method will return JSON object. If false - associative array
$human_friendly | False by default.* Can be *true* or *false*. If true, method will return JSON/array with correct names of maps, correct preset names - like on the right side of picture. E.g. instead of XP_002 there will be "Nansha Strike".
###I don't want to load all of those config files!

That's OK. If you want to use the "light" version of blAPI, just pass true after chosen game.
```php
$blapi = new blAPI("bf4", true);
```
If you want to always use light version of blAPI, you can even delete unused files. **You have to leave all (game)_config_map.php files tho.** These are crucial and class can't work without them.

Important thing: obviously you can change paths of files in $(game)_file_map fields to fit your expectations.
```php
//Captain Obvious: This in an example! You don't have to do this :)

Before:
	private $bf4_file_map =  array (
		"bf4_kit_map" => "classes/cfg/bf4/bf4_kit_map.php",
		"bf4_map_map" => "classes/cfg/bf4/bf4_map_map.php",
		"bf4_dlc_map" => "classes/cfg/bf4/bf4_dlc_map.php",
		"bf4_mode_map" => "classes/cfg/bf4/bf4_mode_map.php",
		"bf4_config_map" => "classes/cfg/bf4/bf4_config_map.php",

	);

After:
	private $bf4_file_map =  array (
		"bf4_config_map" => "maps/bf4_config_map.php",
	);
