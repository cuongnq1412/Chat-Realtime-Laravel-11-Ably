<x-app-layout>
  {{-- paste the code here --}}
  @php
      $loggedIn_userId = auth()->user()->user_id;
      $loggedIn_userName = auth()->user()->name;
  @endphp
  <!-- ========== MAIN CONTENT ========== -->
<!-- Breadcrumb -->
<div class="sticky top-0 inset-x-0 z-20 bg-white border-y px-4 sm:px-6 md:px-8 lg:hidden dark:bg-neutral-800 dark:border-neutral-700">
  <div class="flex justify-between items-center py-2">
    <!-- Breadcrumb -->
    <ol class="ms-3 flex items-center whitespace-nowrap">
      <li class="flex items-center text-sm text-gray-800 dark:text-neutral-400">
        Chat App
        <svg class="flex-shrink-0 mx-3 overflow-visible size-2.5 text-gray-400 dark:text-neutral-500" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M5 1L10.6869 7.16086C10.8637 7.35239 10.8637 7.64761 10.6869 7.83914L5 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </li>
      <li class="text-sm font-semibold text-gray-800 truncate dark:text-neutral-400" aria-current="page">
        Dashboard
      </li>
    </ol>
    <!-- End Breadcrumb -->

    <!-- Sidebar -->

    <!-- End Sidebar -->
  </div>
</div>
<!-- End Breadcrumb -->
<div class="dropdown pt-10">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      Dropdown button
    </button>
    <ul class="dropdown-menu" id="user-list">
        @foreach ($all_users as $user)
            <li  data-id="{{$user->user_id}}" data-name="{{$user->name}}">
                <a class="flex items-center gap-x-3.5 py-2 px-2.5  text-sm text-neutral-700 rounded-lg hover:bg-gray-100 dark:bg-neutral-700 dark:text-white" href="#">
                <img src="https://ui-avatars.com/api/?name={{$user->name}}&rounded=true&background=random" alt="" height="30px" width="30px">
                {{$user->name}}
                </a>
            </li>
        @endforeach
    </ul>
  </div>
<!-- Sidebar -->
{{--  --}}
<!-- End Sidebar -->

<!-- Content -->
<div class="w-full pt-10 px-4 sm:px-6 md:px-8 lg:ps-72">
  <!-- here we create a card ... -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
        <!-- here messages will be published  -->
        <!-- Chat Bubble -->
        <ul class="space-y-5" id="chat-container">


        <!-- End Chat Bubble -->
        </ul>
        <!-- End Chat Bubble -->

        {{-- here the input and button --}}
        <div class="relative mt-5">
            <div class="flex">
                <input type="text"  id="msg-input" class="peer py-3 px-4 pr-12 block w-full bg-gray-200 border-transparent rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:border-transparent dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Enter your message">
                <button type="button" onclick="sendMessage(currentChannel)" class="peer absolute inset-y-0 right-0 flex items-center justify-center bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 text-white font-semibold py-2 px-3 rounded-r-lg shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                        <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- End Content -->
<!-- ========== END MAIN CONTENT ========== -->

<script>
  var ably = new Ably.Realtime.Promise({
      key: '4d5fHg.WXPepQ:AXXlxQ75Vq4OJgEfb_7IwwwzYTKJc_gZ5tSC9TM_Zs4'
   });

    var recipientId = null;
    var currentChannel = null;
    var recipientName = null;
    var login_userId = '<?php echo $loggedIn_userId; ?>';
    // console.log(login_userId);
    var UserList = $('#user-list');

    UserList.on('click', 'li', function() {
        recipientId = $(this).attr('data-id');
        recipientName = $(this).attr('data-name');
        UserList.find('li').removeClass('selected-user');
        $(this).addClass('selected-user');
        console.log(recipientId);


        $.ajax({
            url: '/check-channel',
            method: 'GET',
            data: { recipientId: recipientId },
            success: function(response) {
                if (response.channelExists) {
                    subscribeToChannel(response.channelName);
        // console.log(22);

                } else {
                    createNewChannel(recipientId);
        // console.log(33);

                }
            },
            error: function(xhr, status, error) { console.error(error); }
        });
    });

    function sendMessage(currentChannel) {
        var messageInput = document.getElementById('msg-input');
        var message = messageInput.value.trim();
        // console.log(currentChannel.name);
        // console.log(message);
        // console.log(login_userId);


        if (message !== '') {
            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            $.ajax({
                url: `/save-message/${currentChannel.name}`,
                method: 'POST',
                // contentType: 'application/json',
                data: {
                    conversation_name: currentChannel.name,
                    message_person : login_userId,
                    data: message,
                },
                success: function(response) {
                    if (response.success) {
                        currentChannel.publish(currentChannel.name, { text: message, sender: 'local' });
                        messageInput.value = '';
                    }
                },
                error: function(xhr, status, error) {
        console.error('Error:', xhr.responseText); // In ra thông báo lỗi để dễ dàng theo dõi
    }
            });
        }
    }

    function subscribeToChannel(channelName) {
        if (currentChannel) {
            currentChannel.unsubscribe();
        }
        currentChannel = ably.channels.get(channelName);

        $.ajax({
            url: `/get-messages/${channelName}`,
            method: 'GET',

            success: function(response) {
                if(response.getmessExists){
                response.messages.forEach((msg) => {
                    oldMessage(msg, recipientName);
                    console.log(msg.message_person );
                });
                }else{
                console.log(5);

                }

            }
        });

        currentChannel.subscribe(function(message) {
            displayMessage(message, recipientName);
        });

        $('#chat-container').html('');
    }

    function createNewChannel(recipientId) {

    $.ajax({
        url: '/create-channel',
        method: 'GET',
        data: { recipientId: recipientId },
        success: function (response) {
            if(response.success == true)

            subscribeToChannel(response.channelName);
            else
            console.log(response.error);
        },

    });
}

    function displayMessage(messageObject, recipientName) {
        var isLocalSender = messageObject.connectionId == ably.connection.id;
        const chatContainer = $('#chat-container');

        const message1 = `<li class="max-w-lg flex gap-x-2 sm:gap-x-4 me-11">
            <img class="inline-block size-9 rounded-full" src="https://ui-avatars.com/api/?name=${recipientName}&rounded=true&color=grey" alt="Image Description">
            <div class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3 dark:bg-neutral-900 dark:border-neutral-700">
              <h2 class="font-medium text-gray-800 dark:text-white">
                  ${messageObject.data.text}
              </h2>
            </div>
        </li>`;

        const message2 = `<li class="flex ms-auto gap-x-2 sm:gap-x-4">
            <div class="grow text-end space-y-3">
            <div class="inline-block bg-blue-600 rounded-2xl p-4 shadow-sm">
                <p class="text-sm text-white">${messageObject.data.text}</p>
            </div>
            </div>
            <span class="flex-shrink-0 inline-flex items-center justify-center size-[50px] rounded-full">
            <span class="text-sm font-medium text-white leading-none">
              <img src="https://ui-avatars.com/api/?name=<?php echo $loggedIn_userName; ?>&rounded=true&background=random" alt="" height="50px" width="50px">
            </span>
            </span>
        </li>`;

        chatContainer.append(isLocalSender ? message2 : message1);
    }
    // var isLocalSender = messageObject.connectionId == ably.connection.id;

    function oldMessage(messageObject, recipientName) {
        var isLocalSender = messageObject.message_person == login_userId ;
        const chatContainer = $('#chat-container');

        const message1 = `<li class="max-w-lg flex gap-x-2 sm:gap-x-4 me-11">
            <img class="inline-block size-9 rounded-full" src="https://ui-avatars.com/api/?name=${recipientName}&rounded=true&color=grey" alt="Image Description">
            <div class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3 dark:bg-neutral-900 dark:border-neutral-700">
              <h2 class="font-medium text-gray-800 dark:text-white">
                  ${messageObject.data}
              </h2>
            </div>
        </li>`;

        const message2 = `<li class="flex ms-auto gap-x-2 sm:gap-x-4">
            <div class="grow text-end space-y-3">
            <div class="inline-block bg-blue-600 rounded-2xl p-4 shadow-sm">
                <p class="text-sm text-white">${messageObject.data}</p>
            </div>
            </div>
            <span class="flex-shrink-0 inline-flex items-center justify-center size-[50px] rounded-full">
            <span class="text-sm font-medium text-white leading-none">
              <img src="https://ui-avatars.com/api/?name=<?php echo $loggedIn_userName; ?>&rounded=true&background=random" alt="" height="50px" width="50px">
            </span>
            </span>
        </li>`;

        chatContainer.append(isLocalSender ? message2 : message1);
    }
</script>

</x-app-layout>
