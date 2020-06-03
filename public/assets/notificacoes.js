$(function () {
    if (swRegistration?.pushManager && Notification.permission === "default")
        $(".btn-notify").remove();

    setTimeout(function () {
        db.exeRead("notifications_report").then(notes => {
            for(let note of notes) {
                if (note.recebeu === 0)
                    db.exeCreate("notifications_report", {id: note.id, recebeu: 1});
            }
        });

        $(".badge-notification").remove();
    },500);

    (async () => {
        let myNotifications = await getNotifications();
        if (isEmpty(myNotifications)) {
            $("#notificacoes").htmlTemplate('notificacoesEmpty');
        } else {
            $("#notificacoes").htmlTemplate('note', {notificacoes: myNotifications});
        }
    })();
});