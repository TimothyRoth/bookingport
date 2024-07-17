const webpack = require('webpack');
const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const CleanTerminalPlugin = require('clean-terminal-webpack-plugin');

module.exports = {
    entry: {
        main: './src/js/index.js',
    },
    output: {
        path: path.resolve(__dirname, "dist"),
        filename: 'main.min.js',
    },
    module: {
        rules: [{
            test: /\.(js)$/,
            exclude: /node_modules/,
            use: ['babel-loader']
        },
            {
                test: /\.(scss|css|sass)$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
            },
            {
                test: require.resolve('jquery'),
                use: [{
                    loader: "expose-loader",
                    options: {
                        exposes: ["$", "jQuery"]
                    }
                }]
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery'
        }),
        new CleanTerminalPlugin(),
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: ['dist']
        }),
        new MiniCssExtractPlugin({
            filename: "main.min.css",
        })
    ],
    performance: {
        hints: false
    },
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin(),
            new CssMinimizerPlugin()
        ],
    },
    devtool: "source-map",
    watch: true,
    watchOptions: {
        ignored: '**/node_modules',
        poll: 1000
    },
    mode: "production"
};
