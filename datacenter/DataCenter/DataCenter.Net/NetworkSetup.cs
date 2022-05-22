using DataCenter.Network.HttpWeb;

namespace DataCenter.Network
{
    public static class NetworkSetup
    {
        private static WebServer webServer;

        public static void CreateWebServer(string address, int port)
        {
            webServer = new WebServer(address, port);

            Thread thread = new Thread(webServer.Listen().Wait);
            thread.Start();
        }
    }
}
