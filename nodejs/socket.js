module.exports = function () {
    let module = {};
    let express = require('express');
    let bodyParser = require('body-parser');
    let app = express();
    let execSync = require('child_process').execSync;

    /**
     * Create webSocket for listening on port 7070
     * We will listen for three events: sendMessage, newBet, notifyUser
     */
    module.socketIO = function () {
        app.use(bodyParser.urlencoded({
            extended: true
        }));
        app.use(bodyParser.json());
        let server = app.listen(7070, function () {
            console.log('listening on *:7070')
        });
        let io = require('socket.io')(server);
        const subscriber = require('redis').createClient({
            host: '127.0.0.1',
            port: 6379
        });

        subscriber.on('message', function (channel, message) {
            // пересылаем сообщение из канала redis в комнату socket.io
            io.emit(channel, message);
        });

        io.sockets.on('connection', function (socket) {
            let query = socket.handshake.query;
            if (query.id !== undefined) {
                socket.join('notification_' + query.id);
                subscriber.subscribe('notification_' + query.id);
            }

            socket.join('cartChannel');
            subscriber.subscribe('cartChannel');
            socket.on('userCartAction', function (data) {
                io.to('cartChannel').emit('cartChannel', data);
            });

            socket.on('sendMessage', function (data) {
                socket.join('order_id_' + data.order_id);
                io.to('order_id_' + data.order_id).emit('newMessage', data);
            });
            socket.join('chat');

            socket.on('chatMessage', function (data) {
                io.to('chat').emit('newChatMessage', data);
            });

            socket.join('orderChanged');

            socket.on('orderChangeSuccess', function (data) {
                io.to('orderChanged').emit('orderChanged', data);
            });

            socket.join('permissions_chanel');
            socket.on('setPermissions', function () {
                io.to('permissions_chanel').emit('getPermissions', true);
            });

            socket.on('updateOneS', function () {
                module.executeCommand("php yii sftp/product");
            });
            /**
             * If user close page, we will close socket
             */
            socket.on('disconnect', function () {
                if (!socket.id) {
                    return;
                }
                socket.leave();
            });
        });
    };

    /**
     * Execution of yii2 console command, like "php yii migrate"
     * @param cmd {string} - witch console command will be executed
     */
    module.executeCommand = function (cmd) {
        let options = {
            encoding: 'utf8',
            cwd: '/var/www/salvi/www_api/'
        };

        console.log(execSync(cmd, options));
    };

    return module;
};
