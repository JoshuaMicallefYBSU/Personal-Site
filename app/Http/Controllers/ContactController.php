<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            // Honeypot: a real visitor never fills this field in, since it's hidden from view.
            'company' => ['prohibited'],
        ]);

        Mail::to(config('resume.contact_email'))->send(new ContactMessage(
            senderName: $validated['name'],
            senderEmail: $validated['email'],
            messageBody: $validated['message'],
        ));

        return back()->with('contact_status', 'success');
    }
}
