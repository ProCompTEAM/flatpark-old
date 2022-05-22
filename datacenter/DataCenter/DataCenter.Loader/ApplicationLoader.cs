using DataCenter.Common;

namespace DataCenter.Application
{
    class ApplicationLoader
    {
        static void Main()
        {
            DataCenter.LoadAll();

            DataCenter.Log($"Using Protocol version {Protocol.Version}");
            DataCenter.Log($"Done. {DataCenter.ProductName}, version: {DataCenter.Version}");
        }
    }
}
