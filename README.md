# laravel-social-auth    
Laravel provides an official package named as Socialite to enable an easy implementation of the social login feature. Although, it is absolutely convenient and simple, it still requires some non trivial piece of code.  

Sometimes, the repetitious work can cause an inertia for you to start a new project. To eliminate this issue and quickly gain a momentum for a new idea, this package is built to provide a thin layer wrapping over the Socialite package for the even easier implementation of the social login for a Laravel application.     

## 1 Requirements  

It is recommended to install this package with PHP version 7.1.3+ and Laravel Framework version 5.5+   

## 2 Installation  
    composer require bkstar123/social-auth

## 3 Usage

### 3.1 Default usage

This package is by default assumed to be used together with the Laravel's default authentication guard (as specified in ```config/auth.php```) and ```App\User``` model.  

- Modify Laravel's default user migration to make sure it has the following lines:
```php
$table->string('name')->nullable();
$table->string('avatar')->nullable();
$table->string('email')->unique();
```  

**Note**: If you already run the user migration, you should create another migration to update ```users``` table with these new columns (```name, avatar, email```), for example:  
```php artisan make:migration update_users_table```  

**yyyy_mm_dd_xxxxxxxx_update_users_table.php**:  
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();
            $table->string('email')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('avatar');
            $table->dropColumn('email');
        });
    }
}
```

- Run migration command:
```php artisan migrate```  

- In ```app/User.php```, import and use ```Bkstar123\SocialAuth\Traits\SocialLinkable``` trait  
- In ```app/Http/Controllers/Auth/LoginController.php```, make it to implement ```Bkstar123\SocialAuth\Contracts\SocialAuthentication``` interface, then import and use ```Bkstar123\SocialAuth\Traits\SocialAuthenticable``` trait  
- In ```routes/web.php```, add the following routes:  
```php
Route::get('/login/{provider}', 'Auth\LoginController@redirectToSocialProvider')
     ->name('login.social');
Route::get('/login/{provider}/callback', 'Auth\LoginController@handleSocialProviderCallback')
     ->name('login.social.callback');
```
- In the view file where you have the login form, add a social login link, for example:  
```html
<!-- For Google login -->
<a href="{{ route('login.social', ['provider' => 'google']) }}" 
   class="btn btn-danger">
    <i class="fa fa-google"></i>&nbsp; Google 
</a>
```
- In .env file, add the necessary environment keys/values:  
```
[PROVIDER_NAME]_CLIENT_ID  
[PROVIDER_NAME]_CLIENT_SECRET  
[PROVIDER_NAME]_CLIENT_REDIRECT 
``` 
Where ```[PROVIDER_NAME]``` can be **GOOGLE, FACEBOOK, TWITTER, LINKEDIN, GITHUB, GITLAB, BITBUCKET**.  

### 3.2 Custom usage

#### 3.2.1 If you do not want to use the package default migration:
- Put the following key/value in the .env file:  
```BKSTAR123_SOCIALAUTH_LOAD_MIGRATION=false```

- Then, you must create your own migration file for building a table to store social accounts, for example:  
**yyyy_mm_dd_xxxxxxxx_create_customer_social_accounts_table.php**:  

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerSocialAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_social_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('customer_id');
            $table->string('provider_name')->nullable();
            $table->string('provider_id')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_social_accounts');
    }
}
```

**Note**: ```provider_name```, ```provider_id``` are required to be named as they are in the migration, the foreign key (```customer_id``` in the above example) can be named appropriately depending on your use cases.  
- Run ```php artisan migrate```  

#### 3.2.2 You can define your custom social account model which has a ```belongsTo``` relationship with you custom user model
**app/Models/CustomerSocialAccount.php**:  
```php
<?php

namespace App;

use App\Models\Customer;
use Bkstar123\SocialAuth\Models\Abstracts\SocialAccountBase;

class CustomerSocialAccount extends SocialAccountBase
{
    public function getUserModelClass()
    {
    	return Customer::class;
    }
}
```

#### 3.2.3 You can also tell the package to use your custom social account and user models

For example:  
- In your user model like ```app/Models/Customer.php```:  

After importing and using ```Bkstar123\SocialAuth\Traits\SocialLinkable``` trait, add the following method:  
```php
protected function getSocialAccountModelClass()
{
    return CustomerSocialAccount::class; // Surely, you must autoload this class 
}
```

- In the controller which handles the login logic:  
After making it to implement ```Bkstar123\SocialAuth\Contracts\SocialAuthentication``` interface, importing and using ```Bkstar123\SocialAuth\Traits\SocialAuthenticable``` trait. Add the following methods:  

```php
protected function getUserModelClass()
{
    return Customer::class; // Surely, you must autoload this class 
}

protected function getSocialAccountModelClass()
{
    return CustomerSocialAccount::class; // Surely, you must autoload this class 
}
```

#### 3.2.4 You can change which kind of the social data you want to map to the user which is to be persisted to the database

For example:  
Assuming that your ```users``` table has ```social_avatar```, ```email```. Then, in the controller which handles the login logic, add the following method:  
```php
protected function mapUserWithSocialData($socialUser)
{
    return [
        'social_avatar' => $socialUser->getAvatar(),
        'email' => $socialUser->getEmail(),
    ];
}
```

#### 3.2.5 You can use beforeFirstSocialLogin() hook to add more business logic before signing in a user using her social account at the first time

For example, you may need to set ```email_verified_at``` for a user before signing her in using her social account at the first time if your application enforces a logic that all users must be verified before being able to use some features.  

To do so, in the controller which handles the login logic, add the following method:  
```php
protected function beforeFirstSocialLogin($user, $socialUser)
{
    if (!$user->email_verified_at) {
        $user->email_verified_at = Carbon::now();
        $user->save();
    }
}
```

#### 3.2.6 You can customize the action that is to be taken after successfully signing in a user using her social account

For example: You may want to explicitly redirect the authenticated user to a dashboard  
```php
protected function postSocialLogIn()
{
    return redirect()->route('dashboard');
}
```

#### 3.2.7 You can customize the action that is to be taken if your application fails to get data from the social provider

For example: You may want to redirect the user to the customer login page in this case  
```php
protected function actionIfFailingToGetSocialData()
{
    return redirect()->route('customer.login');
}
```

By default, the package uses the Laravel's default authentication guard which is specified in ```config/auth.php```.  
This behavior can be overwritten by the ```guard()``` method defined in your controller (which handles the login logic):  

For example: You may want the package to use the custom authentication guard named as ```customer```
```php
protected function guard()
{
    return Auth::guard('customer');
}
```
