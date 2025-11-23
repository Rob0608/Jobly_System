<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/

// MAIN AUTH ROUTES
$router->get('/', 'AuthController::userLogin'); // redirects to same as /login
$router->get('/login', 'AuthController::userLogin');
$router->post('/login', 'AuthController::processUserLogin');
$router->get('/logout', 'AuthController::logout');
// Support POST logout from dashboards/forms
$router->post('/logout', 'AuthController::logout');

// ADMIN AUTH
$router->get('/admin', 'AuthController::adminLogin');
$router->post('/admin/login', 'AuthController::processAdminLogin');
$router->get('/admin/logout', 'AuthController::adminLogout');
// Admin actions (approve/delete pending)
$router->post('/admin/approve', 'AdminController::approve');
$router->post('/admin/deletePending', 'AdminController::deletePending');
// Admin edit + activate/deactivate
$router->get('/admin/edit', 'AdminController::edit');
$router->post('/admin/deactivate', 'AdminController::deactivate');
$router->post('/admin/activate', 'AdminController::activate');
// Admin change password
$router->post('/admin/changePassword', 'AdminController::changePassword');

// COMPANY ROUTES
$router->match('/company/register', 'CompanyController::register', ['GET','POST']);
$router->post('/company/save', 'CompanyController::save');
$router->get('/company/verify', 'CompanyController::verify');
$router->post('/company/verify_code', 'CompanyController::verify_code');
$router->get('/company/success', 'CompanyController::success');

$router->match('/company/dashboard', 'AdminController::dashboard', ['GET','POST']);
// Employer / Company actions and employer dashboard
$router->match('/company/employer', 'CompanyController::employerDashboard', ['GET','POST']);
$router->post('/company/update_application', 'CompanyController::updateApplication');
$router->post('/company/post_job', 'CompanyController::postJob');
$router->post('/company/edit_job', 'CompanyController::editJob');
$router->post('/company/delete_job', 'CompanyController::deleteJob');
$router->post('/company/update_profile', 'CompanyController::updateProfile');
$router->post('/company/change_password', 'CompanyController::changePassword');
// company logout - reuse AuthController logout action
$router->get('/company/logout', 'AuthController::logout');
$router->get('/user/dashboard', 'UserController::dashboard');
$router->get('/company/applications', 'ApplicantController::applications');

// APPLICANT ROUTES
$router->get('/applicant/register', 'ApplicantController::register');
$router->post('/applicant/save', 'ApplicantController::save');
$router->get('/applicant/success', 'ApplicantController::success');
$router->get('/applicant/verify', 'ApplicantController::verify');
$router->post('/applicant/verify_code', 'ApplicantController::verify_code');
$router->get('/applicant/dashboard', 'ApplicantController::dashboard');
// Dashboard action routes referenced in view
$router->post('/applicant/profile/save', 'ApplicantController::saveProfile');
$router->post('/applicant/account/update', 'ApplicantController::updateAccount');
$router->post('/applicant/account/change-password', 'ApplicantController::changePassword');
$router->post('/applicant/apply', 'ApplicantController::apply');
$router->post('/applicant/application/delete', 'ApplicantController::deleteApplication');

// SOCIAL AUTH (Google) - Applicants
$router->get('/auth/google', 'SocialAuthController::google');
$router->get('/auth/google/callback', 'SocialAuthController::googleCallback');
$router->post('/auth/google/complete', 'SocialAuthController::googleCompleteApplicant');

// REVIEW SYSTEM ROUTES
$router->post('/review/submit', 'ReviewController::submit');

// FORGOT / RESET PASSWORD
$router->get('/forgot', 'AuthController::forgotForm');
$router->post('/forgot/send', 'AuthController::sendResetCode');
$router->get('/forgot/verify', 'AuthController::forgotVerifyForm');
$router->post('/forgot/verify', 'AuthController::verifyResetCode');
$router->get('/forgot/reset', 'AuthController::forgotResetForm');
$router->post('/forgot/reset', 'AuthController::resetPassword');

// API ROUTES - REST API for Review System
$router->get('/api/health', 'ApiController::health');
$router->get('/api/reviews', 'ApiController::getReviews');
$router->get('/api/reviews/stats', 'ApiController::getStats');
$router->post('/api/reviews/submit', 'ApiController::submitReview');
$router->get('/api/reviews/check', 'ApiController::checkReview');

