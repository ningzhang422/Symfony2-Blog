<?php
// src/Sdz/BlogBundle/Bigbrother/MessagePostEvent.php
 
namespace Sdz\BlogBundle\Bigbrother;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;
 
class MessagePostEvent extends Event
{
	protected $message;
	protected $user;
	protected $autorise;
	
	public function __construct($message, UserInterface $user)
	{
		$this->message  = $message;
    	$this->user     = $user;
	}
	
	// Le listener doit avoir accès au message
	public function getMessage()
	{
		return $this->message;
	}
	
	// Le listener doit pouvoir modifier le message
	public function setMessage($message)
	{
		return $this->message = $message;
	}
	
	// Le listener doit avoir accès à l'utilisateur
	public function getUser()
	{
		return $this->user;
	}
}