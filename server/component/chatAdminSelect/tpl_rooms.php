<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="jumbotron">
    <h1>Chat Room Management</h1>
    <p>
        This page allows to manage chat rooms.
        The card <code>rooms</code> lists all existing chat rooms.
        Selecting a chat room will allow to assign users to the chat room.
        To create a new chat room use the button in the top left corner (if available).
    </p>
    <p>Chat rooms are intended to create topic-specific conversation rooms. As with the general chat, two roles need to be present inside a chat room: at least one <code>Therapist</code> and at least one <code>Subject</code>.</p>
    <ul>
        <li>A <code>Therapist</code> in a chat room sees</li>
        <ul>
            <li>all messages sent by all <code>Subjects</code> in the chat room (the messages are grouped per subject)</li>
            <li>all messages sent by all <code>Therapists</code> in the chat room</li>
        </ul>
        <li>A <code>Subject</code> in a chat room sees</li>
        <ul>
            <li>all messages sent to the <code>Subject</code> by all <code>Therapists</code> in the chat room</li>
            <li>all messages the <code>Subject</code> itself sent to the chat room</li>
        </ul>
    </ul>
    <p>
        A <code>Subject</code> will <strong>not</strong> see any messages sent by other <code>Subjects</code> nor by <code>Therapists</code> addressing other <code>Subjects</code>. Neither in the <code>Lobby</code> (the main room where alle users participate) nor in any other room.
    </p>
</div>
