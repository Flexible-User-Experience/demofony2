<?php

namespace Demofony2\AppBundle\Controller\Front;

use Demofony2\AppBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class CmsController
 */
class CmsController extends Controller
{
    /**
     * @Route("/easy-guide/", name="demofony2_front_cms_easy_guide")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function easyGuideAction()
    {
        $page = $this->getPage('guia-facil');

        return $this->render(':Front/cms:easy_guide.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/regulation/", name="demofony2_front_cms_regulation")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regulationAction()
    {
        $page = $this->getPage('regulation');

        return $this->render(':Front/cms:regulation.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/ita/", name="demofony2_front_cms_ita")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function itaAction()
    {
        $page = $this->getPage('ita');

        return $this->render(':Front/cms:ita.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/uab/", name="demofony2_front_cms_uab")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uabAction()
    {
        $page = $this->getPage('uab');

        return $this->render(':Front/cms:uab.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/transparency-law/", name="demofony2_front_cms_transparency_law")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transparencyLawAction()
    {
        $page = $this->getPage('transparency-law');

        return $this->render(':Front/cms:transparency-law.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/open-government/", name="demofony2_front_cms_open_goverment")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openGovernmentAction()
    {
        $page = $this->getPage('open-government');

        return $this->render(':Front/cms:open-government.html.twig', ['page' => $page]);
    }

    protected function getPage($url)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('Demofony2AppBundle:Page')->findOneBy(array('url' => $url));

        if (!$page) {
            throw $this->createNotFoundException();
        }

        return $page;
    }
}
