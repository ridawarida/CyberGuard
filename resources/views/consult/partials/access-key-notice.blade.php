{{--
    Access-key notice - for Ishrat's intake wizard confirmation page.
    Module 3 feature owner: Johra-E-Jannat Oishy.

    This is the missing link that makes the consultation workspace
    reachable: nothing else in the app currently shows a victim their
    access key. Nothing on Ishrat's side needs to change except adding
    this include (see MODULE3_README.md, section 6).

    Usage, from wherever the tracking code is confirmed after a
    submission - no changes to her Incident model needed:

        $accessKey = \App\Models\Consultation::forIncident($incident->id)->access_key;

    then in that same confirmation view:

        @include('consult.partials.access-key-notice', ['accessKey' => $accessKey])
--}}
<div class="alert alert-info">
    <strong>Save this access key.</strong>
    You'll need it to check on your case, chat securely with a moderator, and
    download your case report later — all without creating an account.

    <div class="mt-2 p-2 bg-white border rounded">
        <code class="cg-access-key-code">{{ $accessKey }}</code>
    </div>

    <p class="small text-muted mb-0 mt-2">
        Keep this key private — anyone who has it can access your consultation.
        There is no way to recover it if it's lost, so write it down or save it
        somewhere safe before you leave this page.
    </p>
</div>

<style>
    .cg-access-key-code { font-size: 14px; }
</style>
