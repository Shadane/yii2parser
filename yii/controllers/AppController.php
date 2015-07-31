<?php

namespace app\controllers;

use Yii;
use app\models\App;
use app\models\AppSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * AppController implements the CRUD actions for App model.
 */
class AppController extends Controller
{


    /**
     * Lists all App models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AppSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = App::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
