<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

   public function before(User $user) {
       return true;
   }

   public function create() {
       return true;
   }

   public function delete() {
       return true;
   }

   public function forceDelete(){
       return false;
   }

   public function update(){
       return true;
   }

   public function view(){
       return true;
   }
}
