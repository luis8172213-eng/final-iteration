@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<section class="relative overflow-hidden bg-slate-50 py-16">
    <div class="absolute -left-20 top-12 h-96 w-104 rounded-full bg-linear-to-br from-pink-300 via-sky-200 to-blue-200 opacity-80 blur-3xl pointer-events-none"></div>
    <div class="absolute right-10 top-28 h-64 w-64 rounded-full bg-linear-to-br from-fuchsia-300 via-cyan-200 to-slate-100 opacity-85 blur-2xl pointer-events-none"></div>
    <div class="relative max-w-6xl mx-auto px-4">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] items-start">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500 mb-4">Contact Us</p>
                <h1 class="text-5xl font-semibold text-slate-800 leading-tight mb-6">Get in touch with the Campus Reserve team.</h1>
                <p class="text-lg leading-8 text-slate-600 mb-8">Have questions about reservations, account access, or campus scheduling? Find our campus location below or prepare a message for the Campus Reserve team.</p>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-800 mb-3">Campus location</h2>
                        <p class="text-slate-600 leading-7">FEU Roosevelt Marikina</p>
                        <p class="text-slate-600 leading-7">J.P. Rizal St., Lamuan, Marikina City</p>
                        <p class="text-slate-600 leading-7">Philippines</p>
                    </div>
                </div>
            </div>

            <div class="rounded-4xl border border-slate-200 bg-white p-8 shadow-2xl">
                <div class="mb-8">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Send a message</p>
                    <h2 class="text-3xl font-semibold text-slate-800 mt-4">How can we help?</h2>
                </div>

                <form class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Your name</label>
                        <input id="name" name="name" type="text" placeholder="Jane Doe" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 focus:border-slate-400 focus:outline-none" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email address</label>
                        <input id="email" name="email" type="email" placeholder="jane.doe@example.com" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 focus:border-slate-400 focus:outline-none" />
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-slate-700 mb-2">Subject</label>
                        <input id="subject" name="subject" type="text" placeholder="Schedule request, account access, other" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 focus:border-slate-400 focus:outline-none" />
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-700 mb-2">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="Write your question here..." class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 focus:border-slate-400 focus:outline-none"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-3xl bg-sky-700 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-sky-600 transition">Submit message</button>
                    <p class="text-sm text-slate-500">This form is interactive for demo purposes. Submission is not yet active.</p>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection