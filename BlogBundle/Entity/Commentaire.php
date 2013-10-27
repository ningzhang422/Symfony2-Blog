<?php

namespace Sdz\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sdz\BlogBundle\Validator\AntiFlood;
/**
 * Commentaire
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sdz\BlogBundle\Entity\CommentaireRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Commentaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="auteur", type="string", length=255)
     */
    private $auteur;

	/**
     * @ORM\Column(name="ip", type="string", length=255)
     */
    private $ip;
	
    /**
     * @var string
     *
     * @ORM\Column(name="contenu", type="text")
	 * @AntiFlood()
     */
    private $contenu;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Sdz\BlogBundle\Entity\Article", inversedBy="commentaires")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $article;


	/**
     * @ORM\ManyToOne(targetEntity="Sdz\UserBundle\Entity\User")
     */
    private $user;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
	public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }
    /**
     * Set auteur
     *
     * @param string $auteur
     * @return Commentaire
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;
    
        return $this;
    }

    /**
     * Get auteur
     *
     * @return string 
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     * @return Commentaire
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;
    
        return $this;
    }

    /**
     * Get contenu
     *
     * @return string 
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Commentaire
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set article
     *
     * @param \Sdz\BlogBundle\Entity\Article $article
     * @return Commentaire
     */
    public function setArticle(\Sdz\BlogBundle\Entity\Article $article)
    {
        $this->article = $article;
    
        return $this;
    }

    /**
     * Get article
     *
     * @return \Sdz\BlogBundle\Entity\Article 
     */
    public function getArticle()
    {
        return $this->article;
    }
	
	/**
     * Set user
     *
     * @param \Sdz\UserBundle\Entity\User $user
     * @return Commentaire
     */
    public function setUser(\Sdz\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Sdz\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
	
	/**
	 * @ORM\PrePersist
	 */
	public function updateActicle_compteurCommentaire()
    {
    	$nbCommentaires = $this->getArticle()->getCompteurCommentaire();
		$this->getArticle()->setCompteurCommentaire($nbCommentaires+1);
    }
	
	/**
   * @ORM\preRemove
   */
  public function decrease()
  {
    $nbCommentaires = $this->getArticle()->getCompteurCommentaire();
    $this->getArticle()->setCompteurCommentaire($nbCommentaires-1);
  }
}