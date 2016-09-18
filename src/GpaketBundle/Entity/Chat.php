<?php

namespace GpaketBundle\Entity;

/**
 * Chat
 */
class Chat
{
    /**
     * @var integer
     */
    private $chat_id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $messages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set chatId
     *
     * @param integer $chatId
     *
     * @return Chat
     */
    public function setChatId($chatId)
    {
        $this->chat_id = $chatId;

        return $this;
    }

    /**
     * Get chatId
     *
     * @return integer
     */
    public function getChatId()
    {
        return $this->chat_id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Chat
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Chat
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Chat
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add message
     *
     * @param \GpaketBundle\Entity\Message $message
     *
     * @return Chat
     */
    public function addMessage(\GpaketBundle\Entity\Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param \GpaketBundle\Entity\Message $message
     */
    public function removeMessage(\GpaketBundle\Entity\Message $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
