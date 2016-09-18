<?php

namespace GpaketBundle\Entity;

/**
 * Message
 */
class Message
{
    /**
     * @var integer
     */
    private $message_id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \GpaketBundle\Entity\Message
     */
    private $reply_to_message;

    /**
     * @var \GpaketBundle\Entity\User
     */
    private $from;

    /**
     * @var \GpaketBundle\Entity\Chat
     */
    private $chat;


    /**
     * Set messageId
     *
     * @param integer $messageId
     *
     * @return Message
     */
    public function setMessageId($messageId)
    {
        $this->message_id = $messageId;

        return $this;
    }

    /**
     * Get messageId
     *
     * @return integer
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Message
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
     * Set text
     *
     * @param string $text
     *
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set replyToMessage
     *
     * @param \GpaketBundle\Entity\Message $replyToMessage
     *
     * @return Message
     */
    public function setReplyToMessage(\GpaketBundle\Entity\Message $replyToMessage = null)
    {
        $this->reply_to_message = $replyToMessage;

        return $this;
    }

    /**
     * Get replyToMessage
     *
     * @return \GpaketBundle\Entity\Message
     */
    public function getReplyToMessage()
    {
        return $this->reply_to_message;
    }

    /**
     * Set from
     *
     * @param \GpaketBundle\Entity\User $from
     *
     * @return Message
     */
    public function setFrom(\GpaketBundle\Entity\User $from = null)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return \GpaketBundle\Entity\User
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set chat
     *
     * @param \GpaketBundle\Entity\Chat $chat
     *
     * @return Message
     */
    public function setChat(\GpaketBundle\Entity\Chat $chat = null)
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * Get chat
     *
     * @return \GpaketBundle\Entity\Chat
     */
    public function getChat()
    {
        return $this->chat;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $logs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add log
     *
     * @param \GpaketBundle\Entity\Log $log
     *
     * @return Message
     */
    public function addLog(\GpaketBundle\Entity\Log $log)
    {
        $this->logs[] = $log;

        return $this;
    }

    /**
     * Remove log
     *
     * @param \GpaketBundle\Entity\Log $log
     */
    public function removeLog(\GpaketBundle\Entity\Log $log)
    {
        $this->logs->removeElement($log);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }
}
