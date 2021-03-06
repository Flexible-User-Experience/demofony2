<?php

namespace Demofony2\AppBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Widop\GoogleAnalytics\Query;
use Widop\GoogleAnalytics\Service;
use Widop\GoogleAnalytics\Response;

/**
 * StatisticsManager
 * @package Demofony2\AppBundle\Manager
 */
class StatisticsManager
{
    protected $em;
    protected $client;
    protected $query;

    /**
     * @param ObjectManager $om
     * @param Service       $service
     * @param Query         $query
     */
    public function __construct(ObjectManager $om, Service $service, Query $query)
    {
        $this->em = $om;
        $this->service = $service;
        $this->query = $query;
        $this->query->setMetrics(array('ga:sessions'));
    }

    public function getVisitsByDay(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getDayRange($date);

        return $this->getGAVisits($startAt, $endAt);
    }

    public function getVisitsByMonth(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getMonthRange($date);

        return $this->getGAVisits($startAt, $endAt);
    }

    public function getVisitsByYear(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getYearRange($date);

        return $this->getGAVisits($startAt, $endAt);
    }

    public function getCommentsPublishedByDay(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getDayRange($date);

        return (int) $this->getCommentsPublished($startAt, $endAt);
    }

    public function getCommentsPublishedByMonth(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getMonthRange($date);

        return (int) $this->getCommentsPublished($startAt, $endAt);
    }

    public function getCommentsPublishedByYear(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getYearRange($date);

        return (int) $this->getCommentsPublished($startAt, $endAt);
    }

    public function getProposalsByDay(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getDayRange($date);

        return (int) $this->getProposalsPublished($startAt, $endAt);
    }

    public function getProposalsByMonth(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getMonthRange($date);

        return (int) $this->getProposalsPublished($startAt, $endAt);
    }

    public function getProposalsByYear(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getYearRange($date);

        return (int) $this->getProposalsPublished($startAt, $endAt);
    }

    public function getVotesByDay(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getDayRange($date);

        return (int) $this->getVotes($startAt, $endAt);
    }

    public function getVotesByMonth(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getMonthRange($date);

        return (int) $this->getVotes($startAt, $endAt);
    }

    public function getVotesByYear(\DateTime $date = null)
    {
        list($startAt, $endAt) = $this->getYearRange($date);

        return (int) $this->getVotes($startAt, $endAt);
    }

    public function getIndexParticipationByDay(\DateTime $date = null)
    {
        return $this->getCommentsPublishedByDay($date) + $this->getProposalsByDay($date) + $this->getVotesByDay($date);
    }

    public function getIndexParticipationByMonth(\DateTime $date = null)
    {
        return $this->getCommentsPublishedByMonth($date) + $this->getProposalsByMonth($date) + $this->getVotesByMonth($date);
    }

    public function getIndexParticipationByYear(\DateTime $date = null)
    {
        return $this->getCommentsPublishedByYear($date) + $this->getProposalsByYear($date) + $this->getVotesByYear($date);
    }

    protected function getGAVisits($startAt, $endAt)
    {
        $this->query->setStartDate($startAt);
        $this->query->setEndDate($endAt);
        /** @var $result \Widop\GoogleAnalytics\Response */
        $result  = $this->service->query($this->query);

        if (count($result->getRows()) > 0) {
            return (int) $result->getRows()[0][0];
        }

        return 0;
    }

    /**
     * Given a datetime, returns two datetimes, one with first second of the given day, and the other with the first second of next day
     * @param \Datetime $date
     *
     * @return array
     */
    protected function getDayRange($date = null)
    {
        $date = !is_object($date) ? new \DateTime('TODAY') : $date;
        $startAt =  \DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", mktime(0, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'))));
        $endAt = clone $startAt;
        $endAt->modify('+1 day');

        return array($startAt, $endAt);
    }

    /**
     * Given a datetime, returns two datetimes, one with first day of the given month, and the other with the first day of next month
     * @param \Datetime $date
     *
     * @return array
     */
    protected function getMonthRange($date = null)
    {
        $date = !is_object($date) ? new \DateTime('TODAY') : $date;
        $startAt =  \DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", mktime(0, 0, 0, $date->format('m'), 1, $date->format('Y'))));
        $endAt = clone $startAt;
        $endAt->modify('+1 month');

        return array($startAt, $endAt);
    }

    /**
     * Given a datetime, returns two datetimes, one with first day of the given year, and the other with the first day of next year
     * @param \Datetime $date
     *
     * @return array
     */
    protected function getYearRange($date = null)
    {
        $date = !is_object($date) ? new \DateTime('TODAY') : $date;
        $startAt =  \DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, $date->format('Y'))));
        $endAt = clone $startAt;
        $endAt->modify('+1 year');

        return array($startAt, $endAt);
    }

    protected function getVotes($startAt, $endAt)
    {
        return  $this->em->getRepository('Demofony2AppBundle:Vote')->getBetweenDate($startAt, $endAt, true);
    }

    protected function getCommentsPublished($startAt, $endAt)
    {
        return  $this->em->getRepository('Demofony2AppBundle:Comment')->getPublishedBetweenDate($startAt, $endAt, true);
    }

    protected function getProposalsPublished($startAt, $endAt)
    {
        return  $this->em->getRepository('Demofony2AppBundle:Proposal')->getPublishedBetweenDate($startAt, $endAt, true);
    }
}
