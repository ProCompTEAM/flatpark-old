using DataCenter.Common.Network.HttpWeb;
using System.Threading;

namespace DataCenter.Common.Network
{
    public static class NetSets
    {
        private static WebServer webServer;

        public static void Initialize()
        {
            CreateWebDataCenter();
        }

        private static void CreateWebDataCenter()
        {
            string address = General.Properties.WebListenerAddress;
            int port = General.Properties.WebListenerPort;

            webServer = new WebServer(address, port);

            Thread thread = new Thread(webServer.Listen().Wait);
            thread.Start();
        }
    }
}
