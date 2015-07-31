<?php

namespace app\controllers;

use Yii;
use app\models\Account;
use app\models\AccountSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;

use yii\helpers\Url;
use yii\base\InvalidCallException;

/**
 * AccountController implements the CRUD actions for Account model.
 */
class AccountController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AccountSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // validate if there is a editable input saved via AJAX
        if (Yii::$app->request->post('hasEditable')) {
            // instantiate your account model for saving
            $accountId = Yii::$app->request->post('editableKey');
            $model = Account::findOne($accountId);

            /* ---------------------------------------------------
             *               переменная $out
             * ---------------------------------------------------
             * То, что вернется пользователю, который редактирует
             * ячейку названия аккаунта в таблице. В данном
             * случае прописываем дефолтные значения.
             * ---------------------------------------------------
             */
            $out = Json::encode(['output' => '', 'message' => '']);

            // fetch the first entry in posted data (there should
            // only be one entry anyway in this array for an
            // editable submission)
            // - $posted is the posted data for Account without any indexes
            // - $post is the converted array for single model validation
            $post = [];
            $posted = current($_POST['Account']);
            $post['Account'] = $posted;

            // load model like any single model validation
            if ($model->load($post)) {
                // can save model or do something before saving model
                $model->save();

                // custom output to return to be displayed as the editable grid cell
                // data. Normally this is empty - whereby whatever value is edited by
                // in the input by user is updated automatically.
                $output = '';

                // specific use case where you need to validate a specific
                // editable column posted when you have more than one
                // EditableColumn in the grid view. We evaluate here a
                // check to see if buy_amount was posted for the Book model
                if (isset($posted['name'])) {
                    $output = $model->name;
                }

                // similarly you can check if the name attribute was posted as well
                // if (isset($posted['name'])) {
                //   $output =  ''; // process as you need
                // }
                $out = Json::encode(['output' => $output, 'message' => '']);
            }
            // return ajax json encoded response and exit
            echo $out;
            return;
        }

        // non-ajax - render the grid by default
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'showAlert' => Yii::$app->session->getFlash('info') ? true : false
        ]);
    }


    /* -----------------------------------------------
     *          actionView не используется.
     * ----------------------------------------------- */
    public function actionView($id)
    {

    }

    /**
     * Creates a new Account model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Account();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'index' page
     * Else it renderAjax 'update'.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        /* ------------------------------------------------
         * Если модель была передана через пост и успешно
         * сохранена, то перенаправляем на главную
         * страницу со списком аккаунтов
         * ------------------------------------------------ */
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('update', [
                'model' => $model
            ]);
        }
    }

    /** -----------------------------------------------------------------
     *                              Delete
     * -----------------------------------------------------------------
     * Получаем $_POST, если в нем есть параметр custom_param - достаем
     * из него ID аккаунта, который нужно удалить,
     * -----------------------------------------------------------------
     */
    public function actionDelete()
    {
        $post = Yii::$app->request->post();
        if (isset($post['custom_param'])) {
            $id = $post['id'];
            if ($this->findModel($id)->delete()) {
                /* если успешно удалено, то выставляем сообщения как для неаяксового запроса, так и для аяксового */
                $msg = 'Аккаунт успешно удален.';
                $out = [
                    'success' => true,
                    'messages' => [
                        'kv-detail-info' => 'Аккаунт # ' . $id . ' Был успешно удален. <a href="' .
                            Url::to(['/account/index']) . '" class="btn btn-sm btn-info">' .
                            '<i class="glyphicon glyphicon-hand-right"></i>  Нажмите сюда</a> для обновления страницы.'
                    ]
                ];
            } else {
                /* если какая-то ошибка при удалении */
                $msg = 'При удалении Аккаунта произошла ошибка, пожалуйста попробуйте позже';
                $out =  [
                    'success' => false,
                    'messages' => [
                        'kv-detail-error' => $msg
                    ]
                ];
            }
                /* если запрос не аяксовый, то выставляем мессадж и делаем редирект */
            if (!Yii::$app->request->isAjax) {
                Yii::$app->getSession()->setFlash('info', $msg);
                return $this->redirect(['index']);
            }
            /* если запрос аяксовый, то выводим json с сообщением о статусе удаления */
            echo Json::encode($out);

            return;

        }
        throw new InvalidCallException("You are not allowed to do this operation. Contact the administrator.");
    }




    /**
     * Finds the Account model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Account the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Account::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
