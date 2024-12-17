<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    // Login Action
    public function actionLogin()
    {
        if (Yii::$app->request->isPost) {
            $email = Yii::$app->request->post('email');
            $password = Yii::$app->request->post('password');
            
            // Verify the user credentials using the verifyUser method
            if ($this->verifyUser($email, $password)) {
                // Store the email in the session
                Yii::$app->session->set('email', $email);
                Yii::$app->session->setFlash('success', 'Login successful!');
                return $this->redirect(['site/dashboard']);
            } else {
                Yii::$app->session->setFlash('error', 'Invalid email or password!');
            }
        }

        return $this->render('login');
    }

    // Method to verify user credentials
    public function verifyUser($email, $password)
    {
        $filePath = Yii::getAlias('@runtime/users.txt');
        
        if (file_exists($filePath)) {
            $users = file($filePath, FILE_IGNORE_NEW_LINES);

            foreach ($users as $user) {
                list($name, $userEmail, $hashedPassword) = explode(',', $user);
                
                if ($userEmail === $email && password_verify($password, $hashedPassword)) {
                    return true;
                }
            }
        }

        return false;
    }

    // Signup Action
    public function actionSignup()
    {
        if (Yii::$app->request->isPost) {
            $name = Yii::$app->request->post('name');
            $email = Yii::$app->request->post('email');
            $password = Yii::$app->request->post('password');

            if (!empty($name) && !empty($email) && !empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userData = "$name,$email,$hashedPassword\n";
                $filePath = Yii::getAlias('@runtime/users.txt');

                file_put_contents($filePath, $userData, FILE_APPEND);

                Yii::$app->session->setFlash('success', 'Signup successful! Please log in.');
                return $this->redirect(['site/login']);
            } else {
                Yii::$app->session->setFlash('error', 'Please fill out all fields.');
            }
        }

        return $this->render('signup');
    }

    // Dashboard Action
    public function actionDashboard()
    {
        $email = Yii::$app->session->get('email');
        $filename = Yii::getAlias('@runtime/transactions_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($email)) . '.txt');
        $transactions = [];

        if (file_exists($filename)) {
            $fileLines = file($filename, FILE_IGNORE_NEW_LINES);

            foreach ($fileLines as $line) {
                $transaction = str_getcsv($line);
                if (count($transaction) === 5) {
                    $transactions[] = $transaction;
                }
            }
        }

        return $this->render('dashboard', ['transactions' => $transactions]);
    }

    public function actionAddTransaction()
    {
        if (Yii::$app->request->isPost) {
            $transactionName = Yii::$app->request->post('transaction-name');
            $category = Yii::$app->request->post('category');
            $amount = Yii::$app->request->post('amount');
            $description = Yii::$app->request->post('description');
            $email = Yii::$app->session->get('email');
            $filename = Yii::getAlias('@runtime/transactions_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($email)) . '.txt');

            $transaction = [
                'name' => $transactionName,
                'category' => $category,
                'amount' => $amount,
                'description' => $description,
                'date' => date('Y-m-d'),
            ];

            $file = fopen($filename, 'a');
            if ($file) {
                $transactionString = implode(',', $transaction) . PHP_EOL;
                fwrite($file, $transactionString);
                fclose($file);

                Yii::$app->session->setFlash('success', 'Transaction added successfully!');
                return $this->redirect(['site/dashboard']);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to save transaction!');
            }
        }
    }

    public function actionLogout()
    {
        Yii::$app->session->destroy();
        return $this->redirect(['site/login']);
    }

    public function actionDeleteTransaction()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isPost) {
            $transactionIndex = Yii::$app->request->post('transactionIndex');
            
            if (!is_numeric($transactionIndex) || intval($transactionIndex) < 0) {
                return ['success' => false, 'message' => 'Invalid transaction index.'];
            }

            $transactionIndex = intval($transactionIndex);
            $email = Yii::$app->session->get('email');
            $filename = Yii::getAlias('@runtime/transactions_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($email)) . '.txt');

            if (!file_exists($filename)) {
                file_put_contents($filename, ''); // Create the file if it doesn't exist
            }

            $transactions = file($filename, FILE_IGNORE_NEW_LINES);

            if (isset($transactions[$transactionIndex])) {
                unset($transactions[$transactionIndex]);
                file_put_contents($filename, implode(PHP_EOL, $transactions) . PHP_EOL);

                return ['success' => true, 'message' => 'Transaction deleted successfully.'];
            } else {
                return ['success' => false, 'message' => 'Transaction not found.'];
            }
        }

        return ['success' => false, 'message' => 'Invalid request method.'];
    }
}
