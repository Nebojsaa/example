<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cloud\Model\Entity\MediaObject;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public static function _isAuthorized($user, $request, $action = null)
    {
        // login-Action is only for not logged in users
        if ($action !== null and $action == 'login' and empty($user)) {
            return true;
        }

        return parent::_isAuthorized($user, $request, $action);
    }

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow();
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = ['contain' => ['Companies', 'Roles', 'MediaObjects']];
        $this->set('pageTitle', 'Nebojsa');
        parent::_index();
        $this->set('columns', [
            'company' => ['title' => __d('cockpit', 'Companies'), 'isEntity' => true],
            'role' => ['title' => __d('cockpit', 'Role'), 'isEntity' => true],
            'firstname' => ['title' => __d('cockpit', 'First name'), 'isEntity' => false],
            'lastname' => ['title' => __d('cock pit', 'Last name'), 'isEntity' => false],
            'username' => ['title' => __d('cockpit', 'Username'), 'isEntity' => false],
            'email' => ['title' => __d('cockpit', 'E-Mail'), 'isEntity' => false],
            'status' => ['title' => __d('cockpit', 'Status'), 'isEntity' => false],
            'media_object' => ['title' => __d('cockpit', 'Media Object'), 'isEntity' => false]
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        parent::_add();
        $companies = $this->Users->Companies->find('list', ['limit' => 200]);
        $roles = $this->Users->Roles->find('list', ['limit' => 200]);
        $this->set(compact('user', 'companies', 'roles'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        parent::_edit($id);
        $companies = $this->Users->Companies->find('list', ['limit' => 200]);
        $roles = $this->Users->Roles->find('list', ['limit' => 200]);
        $this->set(compact('user', 'companies', 'roles'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        parent::_delete($id);
    }

    /**
     * Log the user into the application
     *
     * @return void|\Cake\Network\Response
     */
    public function login()
    {
        if ($this->request->is("post")) {
            $user = $this->Auth->identify();
            if ($user) {
                $user = $this->addUsersAssociated($user);
                $user = $this->addUserDetailsToSession($user);
                $this->Auth->setUser($user);
                return $this->redirect($this->Auth->redirectUrl());
            }

            // User not identified
            $this->Flash->error(__d('cockpit', 'Your username or password is incorrect'));
        }
    }

    public function logout()
    {
        $this->Flash->success(__d('cockpit', 'You are now logged out.'));
        return $this->redirect($this->Auth->logout());
    }

    /**
     * Adds the Role and Company to the loggedin User
     *
     * @param array $user
     */
    protected function addUsersAssociated($user)
    {
        $userTable = TableRegistry::get("Users");
        $results = $userTable->find()
            ->contain(['Roles', 'Companies', 'Companies.Brands', 'Companies.CompanyConfigs'])
            ->where(['Users.id' => $user['id']])
            ->limit(1);
        return $results->first()->toArray();
    }

    /**
     * Adds some User-Details to the Session to be accessible in the whole application
     * e.g.: Last selected Brand, Company-Id, Company-Configuration
     *
     * @param $user
     * @return $user
     */
    protected function addUserDetailsToSession($user)
    {
        // Set company-related stuff
        $this->request->session()->write('Company.id', $user['company']['id']);
        $this->request->session()->write('Company.edition', $user['company']['company_config']['edition']);
        $this->request->session()->write(
            'Company.config.terms',
            json_decode($user['company']['company_config']['terms'])
        );
        $this->request->session()->write(
            'Company.config.colors',
            json_decode($user['company']['company_config']['colors'])
        );

        // Set last Brand if possible.
        if (isset($user['last_brand_id'])
            and !empty($user['last_brand_id'])
        ) {
            $this->request->session()->write('Brand.id', $user['last_brand_id']);
        } else {
            $this->request->session()->write('Brand.id', $user['company']['brands'][0]['id']);
        }

        // Set Company's language
        $availableLanguages = Configure::read('languages');
        if (isset($user['company']['company_config']['language'])
            and !empty($user['company']['company_config']['language'])
            and isset($availableLanguages[$user['company']['company_config']['language']])
        ) {
            $locale = $availableLanguages[$user['company']['company_config']['language']]['locale'];
            $this->request->session()->write("Config.language", $locale);
        }
        return $user;
    }
}
