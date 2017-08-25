<?php
namespace DoctrineGui\Service;

/**
 * @author Brendan <b.nash at southeaster dot com>
 *
 * @Contributors:
 *
 */

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Ramsey\Uuid\Uuid;
use ZF\OAuth2\Doctrine\Entity\AccessToken;
use ZF\OAuth2\Doctrine\Entity\Client;

class AccessTokenService
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     * @access protected
     */
    protected $objectManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $accessTokenRepository;

    /**
     * @param ObjectManager $objectManager
     * @param ObjectRepository $accessTokenRepository
     * @param ScopeService $scopeService
     * @param ClientService $clientService
     */
    public function __construct(
        ObjectManager $objectManager,
        ObjectRepository $accessTokenRepository,
        ScopeService $scopeService,
        ClientService $clientService
    ) {
        $this->objectManager = $objectManager;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->scopeService = $scopeService;
        $this->clientService = $clientService;
    }

    /**
     * @param $client_id
     * @return array
     */
    public function findByClient($client_id)
    {
        return $this->accessTokenRepository->findBy(array('client' => $client_id));
    }

    /**
     * When deleting a client the associated access token must tbe removed too
     * @param Client $clientObject
     */
    public function removeAccessTokens(Client $clientObject)
    {
        $accessTokenArray = $this->findByClient($clientObject->getId());

        foreach ($accessTokenArray AS $accessTokenObject)
        {
            if ($accessTokenObject instanceof AccessToken)
            {

                $this->delete($accessTokenObject);
            }
        }
    }

    /**
     * @param AccessToken $accessTokenObject
     * @return bool
     * @throws \Exception
     */
    public function delete(AccessToken $accessTokenObject)
    {

        try {
            $this->objectManager->remove($accessTokenObject);
            $this->objectManager->flush();
            return true;
        } catch (\Exception $e)
        {
            throw new \Exception($e);
        }

    }

    /**
     * @param AccessToken $accessTokenObject
     * @return AccessToken
     * @throws \Exception
     */
    public function update(AccessToken $accessTokenObject)
    {
        try {
            $this->objectManager->persist($accessTokenObject);
            $this->objectManager->flush();
            return $accessTokenObject;
        } catch (\Exception $e)
        {
            throw new \Exception($e);
        }
    }

    /**
     * @param $params
     * @return AccessToken
     */
    public function createAccessToken($params)
    {
        $clientObject = $this->clientService->findByClientId($params['client_id']);
        $token = new AccessToken();
        $token->setUser($params['user_object']);
        $token->setClient($clientObject);
        $token->setExpires($params['exp']);
        $uuid = Uuid::uuid4();
        $token->setAccessToken($uuid->toString());

        foreach ($params['scope'] AS $scope)
        {
            $scopeObject = $this->scopeService->findByName($scope);
            $token->addScope($scopeObject);
            $scopeObject->addAccessToken($token);
        }

        return $this->update($token);

    }

}
