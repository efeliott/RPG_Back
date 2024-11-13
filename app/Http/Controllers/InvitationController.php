<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Session;
use App\Models\Invitation;
use App\Mail\InvitePlayerToSession;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function invite(Request $request, $sessionToken)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
    
        $email = $request->input('email');
        $session = Session::where('token', $sessionToken)->firstOrFail(); // Rechercher par token
    
        // Créez un token unique pour l'invitation
        $invitationToken = Str::random(60);
    
        // Créez une nouvelle invitation
        $invitation = new Invitation();
        $invitation->session_id = $session->session_id;
        $invitation->email = $email;
        $invitation->token = $invitationToken;
        $invitation->accepted = false;
        $invitation->save();
    
        $link = "https://roleplaygames.online/join-session/{$invitationToken}";
    
        Mail::to($email)->send(new InvitePlayerToSession($session, $link));
    
        return response()->json(['message' => 'Invitation sent successfully.']);
    }

}
