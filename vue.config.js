// vue.config.js
module.exports = {
    devServer: {
        host: '0.0.0.0',
        disableHostCheck: true,
        static: path.resolve(__dirname, 'dist'),
        port: 9000,
        hot: false,
        liveReload: false,
        clientLogLevel: 'info',
    },
};
