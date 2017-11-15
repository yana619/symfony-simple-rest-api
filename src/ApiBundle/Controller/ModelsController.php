<?php
namespace ApiBundle\Controller;

use ApiBundle\Entity\Model;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ModelsController extends FOSRestController
{
    /**
     * @var string
     */
    private $modelName = 'model';

    /**
     * @var string
     */
    private $modelNamePlural = 'models';

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a collection of Models",
     *      statusCodes={
     *         200="Returned when successful",
     *      },
     *      section="Models",
     * )
     *
     * @return Response
     */
    public function getModelsAction()
    {
        $models = $this
            ->getDoctrine()
            ->getRepository('ApiBundle:Model')
            ->findBy(
                [],
                ['name' => 'ASC']
            );

        $view = $this->view($models, 200, true);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a Model by ID",
     *      statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the Models is not found",
     *      },
     *      requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Model ID"}
     *      },
     *      section="Models",
     * )
     *
     * @param $id
     * @return Response
     */
    public function getModelAction($id)
    {
        $model = $this->getModelById($id);

        $view = $this->view($model, 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new Model",
     *      statusCodes={
     *         201="Returned when successful",
     *         400="Returned when parameters are bad",
     *      },
     *      parameters={
     *          {"name"="name", "dataType"="string", "required"=true, "description"="Name of Model"},
     *      },
     *      section="Models",
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function postModelAction(Request $request)
    {
        $model = new Model();
        $model->setName($request->get('name'));

        $errors = $this->get('validator')->validate($model);

        if (count($errors)) {
            throw new HttpException(400, $errors[0]->getMessage());
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($model);
        $em->flush();

        $view = $this->view($model, 201);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Updates a Model",
     *      statusCodes={
     *         200="Returned when successful",
     *         400="Returned when parameters are bad",
     *         404="Returned when the Model is not found",
     *      },
     *      requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Model ID"}
     *      },
     *      parameters={
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Name of Model"},
     *      },
     *      section="Models",
     * )
     *
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function patchModelAction($id, Request $request)
    {
        $model = $this->getModelById($id);

        if ($request->get('name')) {
            $model->setName($request->get('name'));
        }

        $errors = $this->get('validator')->validate($model);

        if (count($errors)) {
            throw new HttpException(400, $errors[0]->getMessage());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $view = $this->view($model, 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Deletes a Model",
     *      statusCodes={
     *         204="Returned when successful",
     *         404="Returned when the Model is not found",
     *      },
     *      requirements={
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Model ID"}
     *      },
     *      section="Models",
     *      views = { "default", "premium" }
     * )
     *
     * @param $id
     * @return Response
     */
    public function deleteModelAction($id)
    {
        $model = $this->getModelById($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($model);
        $em->flush();

        $view = $this->view($model, 204);

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @return Model
     */
    protected function getModelById($id)
    {
        $model = $this->getDoctrine()
            ->getRepository('ApiBundle:Model')
            ->find($id);

        if (!$model instanceof Model) {
            throw new NotFoundHttpException("Model not found.");
        }

        return $model;
    }
}
