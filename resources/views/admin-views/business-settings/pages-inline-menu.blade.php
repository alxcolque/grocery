<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/business-settings/terms-condition') ?'active':'' }}"><a href="{{route('admin.business-settings.terms-condition')}}">{{\App\CPU\translate('Terms_&_Conditions')}}</a></li>
        <li class="{{ Request::is('admin/business-settings/privacy-policy') ?'active':'' }}"><a href="{{route('admin.business-settings.privacy-policy')}}">{{\App\CPU\translate('Privacy_Policy')}}</a></li>
        <li class="{{ Request::is('admin/business-settings/about-us') ?'active':'' }}"><a href="{{route('admin.business-settings.about-us')}}">{{\App\CPU\translate('About_Us')}}</a></li>
        <li class="{{ Request::is('admin/helpTopic/list') ?'active':'' }}"><a href="{{route('admin.helpTopic.list')}}">{{\App\CPU\translate('FAQ')}}</a></li>
    </ul>
</div>
