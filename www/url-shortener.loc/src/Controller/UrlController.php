<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(Request $request): JsonResponse
    {

        $url = new Url();
        $url->setUrl($request->get('url'));

		if($request->get('url') == 'test'){

			$hash = '20241003131249';
			
			// задание 1
			// Добавить ендпоинт, который не декодирует урл, а вместо результата редиректит пользователя на декодированный урл (что-то типа gourl?hash).

			$json = $this->json([
				'gourl' => "decode-url?hash=$hash"
			]);

		} else {
			
			// задание 2
			// Добавить поиск такого же урла в базе при кодировании и, если найден, то использовать его хеш в ответе.

			$urlRepository = $this->getDoctrine()->getRepository(Url::class);
			$hash_ = $urlRepository->findOneByUrl($request->get('url'));

			if (empty ($hash_)) {

				$entityManager = $this->getDoctrine()->getManager();
				$entityManager->persist($url);
				$entityManager->flush();

				$hash = $url->getHash();

				$json = $this->json([
					'hash' => $hash
				]);

			} else {

				$json = $this->json([
					'hash' => $hash_->getHash()
				]);
			}
		}
		
		return $json;
    }

    /**
     * @Route("/decode-url", name="decode_url")
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($request->get('hash'));

        if (isset ($url)) {
			
			// задание 3 10min
			// Добавить срок жизни закодированного урла, по истечению этого срока (через какое-то время после создания) урл не должен декодироваться, а в результате должна выдаваться ошибка.
			
			//print_r($url);
			//exit();
			/*
				App\Entity\Url Object
				(
					[id:App\Entity\Url:private] => 112
					[url:App\Entity\Url:private] => test1.ru
					[hash:App\Entity\Url:private] => 17280194762562
					[createdDate:App\Entity\Url:private] => DateTimeImmutable Object
						(
							[date] => 2024-10-04 12:24:36.000000
							[timezone_type] => 3
							[timezone] => Asia/Tomsk
						)

				)
			*/
			
			$date_test = date('YmdHis', strtotime('-10 minutes', strtotime(date('YmdHis'))));
			$date_sql  = $url->getCreatedDate();
			
			if($date_sql->format('YmdHis') < $date_test){
				
				$json = $this->json([
					'error' => 'exhausted time limit for this hash'
				]);

			} else {

				$json = $this->json([
					'url' => $url->getUrl()
				]);
			}


        } else {
            $json = $this->json([
                'error' => 'Non-existent hash.'
            ]);			
		}
		return $json;
    }
	
	// задание 4
	// Добавить команду, которая отправляет информацию [урл/дата создания] на указанный в конфиге ендпоинт.
	// Здесь нужно учитывать уже отправленные, чтобы не слать всё, а только новые (при повторном запуске).
	// (немного не понял, генерировать самим url или отправлять через get - остановился на генерации, т.к если отправлять через get - то это тоже самое что и encodeUrl() - по итогу добавил это в задании 5 )
    /**
     * @Route("/send", name="send")
     */
	//public function send(Request $request): JsonResponse
	public function send(): JsonResponse
	{
		// уникальный url
		$url_new = 'url_'.time().'_'.rand(999, 9999);

        $url = new Url();
        $url->setUrl($url_new);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($url);
        $entityManager->flush();

		$hash = $url->getHash();

		$json = $this->json([
			'url'  => $url_new,
			'hash' => $hash
		]);
		
		return $json;
	}
	
    /**
     * @Route("/info", name="info")
     */
	/*
	Добавить ещё один сервис (REST API), в котором можно:
	- на некоторый ендпоинт принимать информацию [урл/дата создания];
	- возвращать статистику по кодированным урлам:
	- количество уникальных урлов за заданный промежуток времени;
	*/
	public function info(Request $request): JsonResponse
	{
		
		// /info?add&url=1
		if ($request->get('add') !== null && $request->get('unique') === null){

			$url_new  = $request->get('url');
			
			if(empty($url_new)){

				$json = $this->json([ 'error' => 'empty url, example: /info?add&url=1' ]); 

			} else {

				$urlRepository = $this->getDoctrine()->getRepository(Url::class);
				$hash_ = $urlRepository->findOneByUrl($url_new);
				
				if (empty ($hash_)) {

					$url = new Url();
					$url->setUrl($url_new);

					$entityManager = $this->getDoctrine()->getManager();
					$entityManager->persist($url);
					$entityManager->flush();

					$date = date('Y-m-d H:i:s');
					$hash = $url->getHash();

					$json = $this->json([
						'url'  => $url_new,
						'date' => $date,
						'hash' => $hash
					]);
				
				} else {
					$json = $this->json([
						'hash' => $hash_->getHash(),
						'url'  => 'error: busy'
					]);
				}
			}
		
		// /info?unique
		} else if ($request->get('add') === null && $request->get('unique') !== null){

			// 1. date1 date2
			// /info?unique&date1=20241003000000&date2=20241004000000
			
			// 2. domain .ru
			// /info?unique&domain=ru

			$urlRepository = $this->getDoctrine()->getRepository(Url::class);
			$unique = $urlRepository->DBunique();
			
			$array = array();
			foreach($unique as $key){
				
				$array_save = false;
				
				$date_sql = $key->getCreatedDate()->format('YmdHis');
				if($request->get('date1') !== null && is_numeric($request->get('date1')) && $request->get('date2') !== null && is_numeric($request->get('date2'))){

					
					if($date_sql >= $request->get('date1') && $date_sql <= $request->get('date2')){
						$array_save = true;
					}
					
				}
				
				if($request->get('domain') !== null && strripos($key->getUrl(), '.'.$request->get('domain')) !== false){
					$array_save = true;
				}
				
				if($array_save){

					array_push($array, array(
						'id'   => $key->getId(),
						'url'  => $key->getUrl(),
						'hash' => $key->getHash(),
						'date' => $date_sql)
					);
				}
			}

			if(count($array) > 0){

				$json = $this->json($array);

			} else {
				$json = $this->json([
					'error' => 'no data'
				]);
			}

		} else {
			
			$urlRepository = $this->getDoctrine()->getRepository(Url::class);
			$count = $urlRepository->DBcount();

			$json = $this->json([
				'total' => $count
			]);
		}
		
		return $json;
	}

}
