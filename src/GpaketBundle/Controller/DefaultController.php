<?php

namespace GpaketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
	private $em;
    public function indexAction() {
	    $params = [];
	    $params['counts'] = [];
	    $this->em = $this->getDoctrine()->getManager();

	    $params['counts'] = [
			'groups' => $this->getCount('chat'),
			'messages' => $this->getCount('message'),
			'users' => $this->getCount('user'),
	    ];
        return $this->render('GpaketBundle:Default:index.html.twig', $params);
    }

    private function getCount($entity) {
	    return $this->em->getRepository('GpaketBundle:'.$entity)
		                ->createQueryBuilder('s')
		                ->select('count(s.'.$entity.'_id)')
		                ->getQuery()
		                ->useQueryCache(true)
		                ->useResultCache(true)
		                ->getSingleScalarResult();
    }

	public function getCountAction($entity) {
		$this->em = $this->getDoctrine()->getManager();
		if (in_array($entity, ['chat', 'message', 'user']))
			die($this->getCount($entity));
		else
			die(':P');
	}
}