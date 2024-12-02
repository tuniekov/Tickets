[[+modx.user.id:isloggedin:is=`1`:then=`
<span class="author-subscribe pull-right">
    <label class="checkbox">
        <input type="checkbox" name="" id="tickets2-author-subscribe" value="1" data-id="[[+author_id]]"
               [[+subscribed:notempty=`checked`]]/> [[%tickets2_author_notify]]
    </label>
</span>
`:else=``]]