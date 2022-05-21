using DataCenter.Common;

namespace DataCenter.Loader
{
    class CommonDataCenterLoader
    {
        static void Main()
        {
            General.LoadAll();

            General.Log($"Using Protocol version {Protocol.Version}");
            General.Log($"Done. {General.ProductName}, version: {General.Version}");
        }
    }
}
