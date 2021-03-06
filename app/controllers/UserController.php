<?php

class UserController extends BaseController 
{

    /**
     * Constructor defines filters for actions within this controller.
     *
     * Defined filters are CSRF (for POST requests), guest (only for login and register) and auth (for all except login and register).
     */
    public function __construct()
    {
        $exceptions = array('getLogin', 'postLogin', 'getRegister', 'postRegister', 'getBanned');
        $this->beforeFilter('auth', array('except' => $exceptions));
        $this->beforeFilter('guest', array('only'  => $exceptions));
        $this->beforeFilter('csrf', array('on' => 'post'));
    }

    /**
     * Displays and returns the login view.
     *
     * @return View:login
     */
    public function getLogin()
    {
        return View::make('user.login')
                    ->with('login_error', Session::get('login_error'));
    }

    /**
     * Validates and responds to POST requests to login
     * 
     * @return 302:blog | 302:login
     */
    public function postLogin()
    {
        if (User::attemptLogin(Input::all()))
        {
            // Login successful
            return Redirect::route('home');
        }
        else
        {
            if (isset(User::$bannedUser))
            {
                // Banned
                return Redirect::action('UserController@getBanned')
                    ->with('user', User::$bannedUser);
            }
            else
            {
                // Login unsuccessful
                return Redirect::action('UserController@getLogin')
                            ->with('login_error', true)
                            ->withInput();
            }
        }
    }

    public function getBanned()
    {
        if (!Session::has('user'))
        {
            return Redirect::route('home');
        }

        return View::make('user.banned')
            ->with('ban', Session::get('user')->receivedBans()->get()->last());
    }

    /**
     * Logs the user out
     *
     * @return 302:blog
     */
    function getLogout()
    {
        Auth::logout();
        return Redirect::route('home');
    }

    /**
     * Displays and returns the login view.
     *
     * @return View:register
     */
    public function getRegister()
    { 
        return View::make('user.register');
    }

    /**
     * Validates and responds to POST requests to register
     * 
     * @return 302:blog | 302:register
     */
    public function postRegister()
    {   
        if (User::attemptRegister(Input::all()))
        {
            // Register successful
            return Redirect::route('home');
        }
        else
        {
            // Login unsuccessful
            return Redirect::action('UserController@getRegister')
                        ->withErrors(User::$registerValidator)
                        ->withInput();
        }
    }

    /**
     * Displays and returns the settings view.
     *
     * @return View:settings
     */
    public function getSettings()
    {
        return View::make('user.settings')
                ->with('user', Auth::user())
                ->with('errors', Session::has('errors') ? Session::get('errors') : array())
                ->with('successes', Session::has('successes') ? Session::get('successes') : array());
    }

    /**
     * Validates and responds to POST requests to change settings
     * 
     * @return 302:settings
     */
    public function postSettings()
    {
        $data = Input::all();

        $errors = array();
        $successes = array();

        $user = Auth::user();

        // If the name has been changed
        if (Input::get('name') != $user->display_name)
        {
            if ($user->attemptUpdateName($data))
            {
                array_push($successes, 'Display name changed successfully');
            }
            else
            {
                array_push($errors, $user->updateNameValidator->messages()->first());
            }
        }

        // If the password has been changed
        if(Input::get('cur_password') != '' || Input::get('new_password') != '')
        {
            if ($user->attemptUpdatePassword($data))
            {
                array_push($successes, 'Password changed successfully');
            }
            else
            {
                array_push($errors, $user->updatePasswordValidator->messages()->first());
            }
        }

        return Redirect::action('UserController@getSettings')
                    ->with('errors', $errors)
                    ->with('successes', $successes);
    }

    /**
     * Validates and responds to POST requests to change the display name
     * 
     * @return JSON:response
     */
    public function postChangeUsername()
    {
        // TODO: Implement

        echo "hi";
    }

    /**
     * Validates and responds to POST requests to change the password
     * 
     * @return JSON:response
     */
    public function postChangePassword()
    {
        // TODO: Implement

        echo "hi";
    }
}