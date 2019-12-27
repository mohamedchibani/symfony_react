<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert; 
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

use App\Controller\ResetPasswordAction;


/**
 * @ApiResource(
 *     
 *     itemOperations={
 *          "GET" = {
 *              "access_control" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *              "normalization_context" = {
 *                   "groups" = {"get"}
 *              }
 *          },
 * 
 *          "PUT" = {
 *              "access_control" = "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "denormalization_context" = {
 *                   "groups" = {"put"}
 *              },
 *              "normalization_context" = {
 *                   "groups" = {"get"}
 *              }
 *          },
 *          
 *          "put-reset-password"= {
 *              "access_control" = "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "method" = "PUT",
 *              "path" = "/users/{id}/reset-password",
 *              "controller" = ResetPasswordAction::class,
 *              "denormalization_context" = {
 *                   "groups" = {"put-reset-password"}
 *              }, 
 *           },
 * 
 *          "DELETE" = {
 *              "access_control" = "is_granted('ROLE_SUPERADMIN')"
 *          }
 *     },
 *     
 *     collectionOperations={"GET",
 *          "POST" = {
 *               "denormalization_context" = {
 *                   "groups" = {"post"}
 *               },
 *                "normalization_context"= {
 *                    "groups" = {"get"}
 *                }  
 *          }
 *     },
 *      
 *     
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *      fields={"username","email"}
 * )
 */
class User implements UserInterface
{

    const ROLE_COMMENTATOR = "ROLE_COMMENTATOR";
    const ROLE_WRITER = "ROLE_WRITER";
    const ROLE_EDITOR = "ROLE_EDITOR";
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_SUPERADMIN = "ROLE_SUPERADMIN";

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get","post","get-comment-with-author","get-post-with-author"})
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Regex(pattern="/^[a-z]+$/i", message="Ce message ne respecte pas le pattern", groups={"post"})
     * @Assert\Length(min=5, max=20, minMessage="Ce champs doit comporte au moins {{ limit }} chars", maxMessage="Ce champs ne doit pas dépasser {{ limit }} chars", groups={"post"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post"})
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Expression(
     *      "this.getPassword() === this.getRetypedPassword()",
     *      message="Password does not matched",
     *      groups={"post"}
     * )
     * @Groups({"post"})
     */
    private $retypedPassword;

    /**
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"put-reset-password"})
     */
    private $newPassword;

    /**
     * @Assert\NotBlank()
     * @Assert\Expression(
     *      "this.getNewPassword() === this.getNewRetypedPassword()",
     *      message="Password does not matched"
     * )
     * @Groups({"put-reset-password"})
     */
    private $newRetypedPassword;

     /**
     * @Assert\NotBlank()
     * @UserPassword()
     * @Groups({"put-reset-password"})
     */
    private $oldPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post","get","put","get-comment-with-author","get-post-with-author"})
     * @Assert\NotBlank(message="ce champs est oblogatoire", groups={"post", "put"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post","put","get-post-with-author"})
     * @Assert\NotBlank(groups={"post", "put"})
     * @Assert\Email(groups={"post", "put"})
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author")
     * @Groups({"get"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"get"})
     */
    private $comments;

    /**
     * @ORM\Column(type="simple_array", length=200, nullable=true)
     */
    private $roles;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles(): array{
        return $this->roles;
    }

    public function setRoles(array $roles){
       $this->roles = $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt(){
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(){
        
    }


    public function getRetypedPassword(): ?string{
        return $this->retypedPassword;
    }


    public function setRetypedPassword(string $retypedPassword): self
    {
        $this->retypedPassword = $retypedPassword;

        return $this;
    }



    public function getNewPassword(): ?string{
        return $this->newPassword;
    }

    
    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }



    public function getNewRetypedPassword(): ?string{
        return $this->newRetypedPassword;
    }


    public function setNewRetypedPassword(string $newRetypedPassword): self
    {
        $this->newRetypedPassword = $newRetypedPassword;

        return $this;
    }



    public function getOldPassword(): ?string{
        return $this->oldPassword;
    }


    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function __toString():string
    {
        return $this->name;
    }



}
