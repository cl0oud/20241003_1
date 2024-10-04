# README #

This is a test work from Web Department of Unigine Company.

### How do I get set up? ###

* Install Docker Compose
* Install Git
* Clone this repository
* Put ```127.0.0.1 url-shortener.loc``` into your hosts file
* Run ```docker-compose up``` in the root of the repository
* Go to ```http://url-shortener.loc``` in your browser

### How do I use it? ###

* To encode ```someurl``` you can use ```/encode-url?url=someurl``` endpoint
* To decode ```somehash``` you can use ```/decode-url?hash=somehash``` endpoint


1. docker-compose up -d
2. docker exec container_php -it bash
3. composer install --prefer-source --no-interaction && composer update && exit
4. задание 1 encodeUrl() /src/Controller/UrlController.php
	/encode-url?url=test
		{"gourl":"decode-url?hash=20241003131249"}
5. задание 2 encodeUrl() /src/Repository/UrlRepository.php + /src/Controller/UrlController.php + /vendor/doctrine/orm/src/AbstractQuery.php
	/encode-url?url=test2
		{"hash":"20241003124626"}
6. задание 3 decodeUrl() /src/Controller/UrlController.php
	/decode-url?hash=20241003124626
		{"error":"exhausted time limit for this hash"}
7. задание 4 send()      /src/Controller/UrlController.php + /src/Entity/Url.php
	/send
		{"url":"url_1728029188_2290","hash":"17280291883190"}
8. задание 5 info()      /src/Controller/UrlController.php + /src/Repository/UrlRepository.php
	/info
		{"total":"113"}
		
	/info?unique&date1=20241004000000&date2=20241004240000
		[{"id":113,"url":"url_1728029188_2290","hash":"17280291883190","date":"20241004150628"},{"id":112,"url":"test1.ru","hash":"17280194762562","date":"20241004122436"},{"id":111,"url":"url_1728016055_9121","hash":"17280160553587","date":"20241004112735"},{"id":110,"url":"1","hash":"17280128966282","date":"20241004103456"}]
		
	/info?unique&domain=ru
		[{"id":112,"url":"test1.ru","hash":"17280194762562","date":"20241004122436"}]
