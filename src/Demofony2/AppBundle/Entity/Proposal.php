<?php
/**
 * Demofony2
 * 
 * @author: Marc Morales Valldepérez <marcmorales83@gmail.com>
 * 
 * Date: 13/11/14
 * Time: 16:52
 */
namespace Demofony2\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Demofony2\UserBundle\Entity\User;

/**
 * Proposal
 *
 * @ORM\Table(name="demofony2_proposal")
 * @ORM\Entity
 */
class Proposal extends ParticipationBaseAbstract
{
    /**
     * @ORM\ManyToMany(targetEntity="Demofony2\AppBundle\Entity\Image")
     * @ORM\JoinTable(name="demofony2_proposal_images",
     *      joinColumns={@ORM\JoinColumn(name="proposal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    protected  $images;

    /**
     * @ORM\ManyToMany(targetEntity="Demofony2\AppBundle\Entity\Document")
     * @ORM\JoinTable(name="demofony2_proposal_documents",
     *      joinColumns={@ORM\JoinColumn(name="proposal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    protected  $documents;

    /**
     * @ORM\ManyToOne(targetEntity="Demofony2\UserBundle\Entity\User", inversedBy="proposals")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $author;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set author
     *
     * @param User $author
     * @return Proposal
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
