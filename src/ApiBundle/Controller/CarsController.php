<?php
namespace ApiBundle\Controller;

use ApiBundle\Entity\Car;
use ApiBundle\Entity\Model;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CarsController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a collection of Cars",
     *      statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the Model is not found",
     *      },
     *      requirements={
     *          {"name"="modelId", "dataType"="integer", "requirement"="\d+", "description"="Model ID"}
     *      },
     *      section="Cars",
     * )
     *
     * @param $modelId
     * @return Response
     */
    public function getCarsAction($modelId)
    {
        $model = $this->getCarModelById($modelId);

        $view = $this->view($model->getCars(), 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a Car by ID",
     *      statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the car is not found",
     *      },
     *      requirements={
     *          {"name"="modelId", "dataType"="integer", "requirement"="\d+", "description"="Model ID"},
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Car ID"}
     *      },
     *      section="Cars",
     * )
     *
     * @param $modelId
     * @param $id
     * @return Response
     */
    public function getCarAction($modelId, $id)
    {
        $car = $this->getCar($modelId, $id);

        $view = $this->view($car, 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Creates a new Car",
     *      statusCodes={
     *         201="Returned when successful",
     *         400="Returned when parameters are bad",
     *      },
     *      parameters={
     *          {"name"="name", "dataType"="string", "required"=true, "description"="Model of Car"},
     *          {"name"="price", "dataType"="integer", "required"=true, "description"="Price of Car"}
     *      },
     *     requirements={
     *          {"name"="modelId", "dataType"="integer", "requirement"="\d+", "description"="Model ID"}
     *      },
     *      section="Cars",
     * )
     *
     * @param $modelId
     * @param Request $request
     * @return Response
     */
    public function postCarAction($modelId, Request $request)
    {
        $model = $this->getCarModelById($modelId);

        $car = new Car();
        $car->setName($request->get('name'));
        $car->setPrice($request->get('price'));
        $car->setModel($model);

        $errors = $this->get('validator')->validate($car);

        if (count($errors)) {
            throw new HttpException(400, $errors[0]->getMessage());
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($car);
        $em->flush();

        $view = $this->view($car, 201);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Updates a Car",
     *      statusCodes={
     *         200="Returned when successful",
     *         400="Returned when parameters are bad",
     *         404="Returned when the car is not found",
     *      },
     *      requirements={
     *          {"name"="modelId", "dataType"="integer", "requirement"="\d+", "description"="Model ID"},
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Car ID"}
     *      },
     *      parameters={
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Name of Car"},
     *          {"name"="price", "dataType"="integer", "required"=false, "description"="Price of Car"}
     *      },
     *      section="Cars",
     * )
     *
     * @param $modelId
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function patchCarAction($modelId, $id, Request $request)
    {
        $car = $this->getCar($modelId, $id);

        if ($request->get('name')) {
            $car->setName($request->get('name'));
        }

        if ($request->get('price')) {
            $car->setPrice($request->get('price'));
        }

        $errors = $this->get('validator')->validate($car);

        if (count($errors)) {
            throw new HttpException(400, $errors[0]->getMessage());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $view = $this->view($car, 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      description="Deletes a Car",
     *      statusCodes={
     *         204="Returned when successful",
     *         404="Returned when the car is not found",
     *      },
     *      requirements={
     *          {"name"="modelId", "dataType"="integer", "requirement"="\d+", "description"="Model ID"},
     *          {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="Car ID"}
     *      },
     *      section="Cars",
     *      views = { "default", "premium" }
     * )
     *
     * @param $id
     * @param $modelId
     * @return Response
     */
    public function deleteCarAction($modelId, $id)
    {
        $car = $this->getCar($modelId, $id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($car);
        $em->flush();

        $view = $this->view($car, 204);

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @param $modelId
     * @return Car
     */
    protected function getCar($modelId, $id)
    {
        $car = $this->getDoctrine()
            ->getRepository('ApiBundle:Car')
            ->find($id);

        if (!$car instanceof Car || $car->getModel()->getId() != $modelId) {
            throw new NotFoundHttpException("Car not found.");
        }

        return $car;
    }

    /**
     * @param $id
     * @return Model
     */
    protected function getCarModelById($id)
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
