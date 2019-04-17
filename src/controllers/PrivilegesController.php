<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\privileges
 * @category   CategoryName
 */

namespace lispa\amos\privileges\controllers;


use lispa\amos\privileges\AmosPrivileges;
use lispa\amos\privileges\utility\PrivilegesUtility;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

use lispa\amos\privileges\assets\AmosPrivilegesAsset;
use Yii;

/**
 * Class PrivilegesController
 * @package lispa\amos\privileges\controllers
 */
class PrivilegesController extends Controller
{

    /**
     * @var string $layout Layout per la dashboard interna.
     */
    public $layout = 'main';

    /**
     * @var DbManager $authManager
     */
    protected $authManager = null;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {

        $rolesEnabled = [
            'PRIVILEGES_MANAGER'
        ];

        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'manage-privileges',
                            'enable',
                            'disable',
                            'save-domains'
                        ],
                        'roles' => $rolesEnabled,
                    ],
                ],
                'denyCallback' => function ($rule) {
                    if (\Yii::$app->getUser()->isGuest) {
                        \Yii::$app->getSession()->addFlash('warning',
                            AmosPrivileges::t('amosprivileges', 'Session expired, please log in.'));
                        \Yii::$app->getUser()->loginRequired();
                    }
                    throw new ForbiddenHttpException(
                        AmosPrivileges::t('amosprivileges', 'You are not authorized to view this page')
                    );
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setUpLayout();
        
        AmosPrivilegesAsset::register(Yii::$app->view);
        
        $this->authManager = \Yii::$app->authManager;
    }

    /**
     * @param integer $id - The user id for whom we are managing privileges
     * @return string - the rendered view
     */
    public function actionManagePrivileges($id)
    {
        $cwhNodes = [];
        $utility = new PrivilegesUtility(['userId' => $id]);
        $array = $utility->getPrivilegesArray( false);

        $cwhModule = \Yii::$app->getModule('cwh');
        if($cwhModule) {
            $listNetworks = $cwhModule->getUserNetworks($id);
            if(!empty($listNetworks)) {
                $cwhNodes = ArrayHelper::map($listNetworks, 'id', 'text');
            }
        }
        return $this->render('manage-privileges', [
            'userId' => $id,
            'array' => $array,
            'cwhNodes' => $cwhNodes
        ]);

    }

    /**
     * @param $userId
     * @param $priv
     * @param $type
     * @param bool $isCwh
     * @param string $anchor
     * @return \yii\web\Response
     */
    public function actionEnable($userId, $priv, $type, $isCwh = false, $anchor = '')
    {
        if(!$isCwh) {
            $authManager = \Yii::$app->authManager;
            if ($type == Item::TYPE_ROLE) {
                $privilege = $authManager->getRole($priv);
            } else {
                $privilege = $authManager->getPermission($priv);
            }
            $authManager->assign($privilege, $userId);
        }
        return $this->redirect(['manage-privileges', 'id' => $userId, '#' => $anchor]);
    }

    /**
     * @param $userId
     * @param $priv
     * @param $type
     * @param bool $isCwh
     * @param string $anchor
     * @return \yii\web\Response
     */
    public function actionDisable($userId, $priv, $type, $isCwh = false, $anchor = '')
    {
        if(!$isCwh) {
            $authManager = \Yii::$app->authManager;
            if($type == Item::TYPE_ROLE){
                $privilege = $authManager->getRole($priv);
            } else {
                $privilege = $authManager->getPermission($priv);
            }
            $authManager->revoke($privilege, $userId);
        } else {
            $cwhAuthAssigns = \lispa\amos\cwh\models\CwhAuthAssignment::find()->andWhere(['item_name' => $priv, 'user_id' => $userId])->all();
            foreach ($cwhAuthAssigns as $cwhAuthAssign){
                $cwhAuthAssign->delete();
            }
        }
        return $this->redirect(['manage-privileges', 'id' => $userId, '#' => $anchor]);
    }

    /**
     * @param $userId
     * @param string $anchor
     * @return \yii\web\Response
     */
    public function actionSaveDomains($userId, $anchor = '')
    {
        $post = \Yii::$app->request->post();
        if(!empty($post['auth-assign'])){
            $authAssign = $post['auth-assign'];

            $newDomains = !empty($authAssign['newDomains'])? $authAssign['newDomains'] : [] ;
            $savedDomainsString = $authAssign['savedDomains'];
            $savedDomains =  !empty($savedDomainsString)? explode(',',$savedDomainsString) : [];
            $itemName = $authAssign['class_name'];
            //check if a domain is to delete or to be added or do nothing
            if(!empty($newDomains)){
                foreach ($newDomains as $newDomain){
                    if(empty($savedDomains) || !in_array($newDomain, $savedDomains)){
                        //if no domain is saved or if a domain has been added we save a new chw auth assignment row
                        $authAssignRow = new \lispa\amos\cwh\models\CwhAuthAssignment([
                            'user_id' => $userId,
                            'item_name' => $itemName,
                            'cwh_nodi_id' => $newDomain
                        ]);
                        $authAssignRow->save(false);
                    }
                }
            }
            if(!empty($savedDomains)){
                foreach ($savedDomains as $savedDomain){
                    if(empty($newDomains) || !in_array($savedDomain, $newDomains)){
                        //if one or all domains have been removed we delete the cwh auth assignment row
                        $authAssignRow = \lispa\amos\cwh\models\CwhAuthAssignment::findOne([
                            'user_id' => $userId,
                            'item_name' => $itemName,
                            'cwh_nodi_id' => $savedDomain
                        ]);
                        $authAssignRow->delete();
                    }
                }
            }
        }
        return $this->redirect(['manage-privileges', 'id' => $userId, '#' => $anchor]);
    }

    /**
     * @param null $layout
     * @return bool
     */
    public function setUpLayout($layout = null)
    {
        if ($layout === false) {
            $this->layout = false;
            return true;
        }
        $this->layout = (!empty($layout)) ? $layout : $this->layout;
        $module = \Yii::$app->getModule('layout');
        if (empty($module)) {
            if (strpos($this->layout, '@') === false) {
                $this->layout = '@vendor/lispa/amos-core/views/layouts/'.(!empty($layout) ? $layout : $this->layout);
            }
            return true;
        }
        return true;
    }
}