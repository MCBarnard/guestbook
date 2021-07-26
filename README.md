# Guest Book
A guest book system

## Setup locally
* `git clone`
* `php artisan key:generate`
* `php artisan migrate`
* `php artisan db:seed --class=CreateUsersSeeder`
-------------------------------------------------
##### Optional to add Messages
* `php artisan db:seed --class=CreateMessagesSeeder`
-------------------------------------------------
* `npm install`
* `npm run dev`

-------------------------------------------------
##### PayFast Specific
* `cd /srv`
* `./tools-new-website.sh`
* inside your guestbook.conf (srv-lb) change `apache74ssl` -> `apache72ssl`
* Add url to hosts file `127.0.0.1    guestbook.pf.[tld]`
-------------------------------------------------
### Default username and passwords as per the seeder:

#### Basic User
username: user@test.com
password: 1qazxsw2

Can only delete their own history and edit their own messages

#### Admin User
username: admin@test.com
password: 1qazxsw2

Can delete anyone's chats and edit anyone's messages
