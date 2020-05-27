<?php

namespace App\Security;

use App\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;

class MyEntityUserProvider extends EntityUserProvider {

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {   
        dd($response);
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        $serviceName = $response->getResourceOwner()->getName();
        $setterId = 'set'. ucfirst($serviceName) . 'ID';
        $setterAccessToken = 'set'. ucfirst($serviceName) . 'AccessToken';

        // unique integer
        $username = $response->getUsername();
        if (null === $user = $this->findUser([$this->properties[$resourceOwnerName] => $username])) {
            $user = new User();

            $user->$setterId($username);
            $user->$setterAccessToken($response->getAccessToken());

            $user->setLastName($response->getLastname());
            $user->setFirstName($response->getFirstname());
        }
        $user->setEmail($response->getEmail());
        
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}