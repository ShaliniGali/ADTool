
<div class="p-3">

<p> By logging in to this website: </p>
    <ol>
        <li> Users are accessing a U.S. Government information system. </li>
        <li> Information system usage may be monitored, recorded, and subject to audit. </li>
        <li> Unauthorized use of the information system is prohibited and subject to criminal and civil penalties. </li>
        <li> Use of the information system indicates consent to monitoring and recording. </li>
    </ol>

	<a href="/rb_kc/authenticate/0/default" class="btn btn-success mt-4 w-100">Login</a>

    <?php
        if(RB_KEYCLOAK_DEBUG==="TRUE"){
            echo '<a href="/rb_sp" class="btn btn-success mt-4 w-100"><i class="fas fa-bug mr-3 fa-fw"></i>SAML Debugger</a>';
        }
    ?>

</div>
