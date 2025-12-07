<?php

namespace backend\controllers;

use common\models\Apple;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TesttaskController implements the CRUD actions for Apple model.
 */
class TesttaskController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'fall' => ['post'],
                    'eat' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Apple models.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Перед показом списка актуализируем состояние гнилости яблок
        $justRottenIds = [];
        /** @var Apple[] $allApples */
        $allApples = Apple::find()->all();
        foreach ($allApples as $apple) {
            $wasRotten = ((int)$apple->status === Apple::STATUS_ROTTEN);
            if (!$wasRotten && $apple->isRotten()) {
                // isRotten() внутри обновит статус/rotten_at при необходимости
                $justRottenIds[] = $apple->id;
            }
        }

        if (!empty($justRottenIds)) {
            \Yii::$app->session->setFlash(
                'warning',
                'Испортились яблоки с номерами: #' . implode(', #', $justRottenIds) . '.'
            );
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Apple::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Apple model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Apple();

        $model->loadDefaultValues();



        if ($model->save()) {
            \Yii::$app->session->setFlash('success', 'Яблоко #' . $model->id . ' успешно создано.');
        } else {
            $error = implode('; ', $model->getFirstErrors());
            \Yii::$app->session->setFlash('danger', 'Не удалось создать яблоко: ' . $error);
        }

        return $this->redirect(['index']);
    }

    /**
     * Упасть яблоку с дерева.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionFall($id)
    {
        $model = $this->findModel($id);

        try {
            $model->fallToGround();
            \Yii::$app->session->setFlash('success', 'Яблоко #' . $model->id . ' упало на землю.');
        } catch (\DomainException $e) {
            \Yii::$app->session->setFlash('danger', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Съесть часть яблока.
     * @param int $id
     * @param int $percent
     * @return \yii\web\Response
     */
    public function actionEat($id, $percent)
    {
        $model = $this->findModel($id);

        try {
            $fullyEaten = $model->eat((int)$percent);
            if ($fullyEaten) {
                \Yii::$app->session->setFlash('success', 'Яблоко #' . $id . ' полностью съедено и удалено.');
            } else {
                \Yii::$app->session->setFlash('success', 'Откушено ' . (int)$percent . '% яблока #' . $model->id . '.');
            }
        } catch (\DomainException $e) {
            if ($model->isRotten()) {
                \Yii::$app->session->setFlash('danger', 'Яблоко #' . $model->id . ' испортилось. Съесть нельзя.');
            } else {
                \Yii::$app->session->setFlash('danger', $e->getMessage());
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Apple model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Apple the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Apple::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
