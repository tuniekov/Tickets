[[+modx.user.id:isloggedin:is=`1`:then=`
<span class="tickets2-subscribe pull-right">
    <label for="tickets2-subscribe" class="checkbox">
        <input type="checkbox" name="" id="tickets2-subscribe" value="1" data-id="[[*id]]"
               [[+subscribed:notempty=`checked`]]/> [[%tickets2_section_notify]]
    </label>
</span>
`:else=``]]

<div class="tickets2-list">
    [[+output]]
</div>