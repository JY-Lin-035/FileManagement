@component('mail::message')
# Meow~

請點擊下方按鈕驗證您的電子信箱地址：

@component('mail::button', ['url' => $url])
驗證電子信箱地址
@endcomponent

如果您沒有申請帳號，請忽略此信件。

感謝您使用我們的服務！
{{ 'Meow~' }}
@endcomponent
