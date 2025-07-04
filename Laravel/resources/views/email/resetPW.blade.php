@component('mail::message')
# Meow~

請點擊下方按鈕重置密碼：

@component('mail::button', ['url' => $url])
重置密碼
@endcomponent

如果您沒有使用過忘記密碼功能，請忽略此信件並保護好您的密碼😉。

感謝您使用我們的服務！
{{ 'Meow~' }}
@endcomponent
